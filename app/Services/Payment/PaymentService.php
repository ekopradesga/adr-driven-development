<?php

namespace App\Services\Payment;

use App\Domain\Events\PaymentCompleted;
use App\Domain\Events\PaymentFailed;
use App\Domain\Events\PaymentFullyAllocated;
use App\Domain\Events\PaymentIntentCreated;
use App\Domain\Events\PaymentPartiallyAllocated;
use App\Domain\Events\PaymentReceived;
use App\Domain\Events\PaymentRecorded;
use App\Domain\Events\PaymentReversed;
use App\Domain\Events\PaymentValidated;
use App\Enums\PaymentAllocationStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Services\AbstractCrudService;
use App\Services\Billing\InvoiceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService extends AbstractCrudService
{
    protected string $modelClass = Payment::class;

    public function __construct(
        private readonly InvoiceService $invoiceService
    ) {}

    public function create(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::lockForUpdate()->findOrFail($data['customer_id']);

            $payment = Payment::create([
                'payment_number' => $this->generatePaymentNumber($data['payment_date'] ?? now()->toDateString()),
                'customer_id' => $customer->id,
                'status' => PaymentStatus::IntentCreated->value,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'IDR',
                'method' => $data['method'],
                'channel_reference' => $data['channel_reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            event(new PaymentIntentCreated($payment->id, $payment->customer_id, auth()->id()));

            return $payment->fresh(['customer', 'receiver']);
        });
    }

    public function update($payment, array $data): Payment
    {
        return DB::transaction(function () use ($payment, $data) {
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);

            if (!in_array($payment->status, [PaymentStatus::IntentCreated, PaymentStatus::WaitingPayment], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Only intent_created or waiting_payment payments may be edited.',
                ]);
            }

            $payment->update([
                'payment_date' => $data['payment_date'] ?? $payment->payment_date,
                'amount' => $data['amount'] ?? $payment->amount,
                'currency' => $data['currency'] ?? $payment->currency,
                'method' => $data['method'] ?? $payment->method,
                'channel_reference' => $data['channel_reference'] ?? $payment->channel_reference,
                'notes' => $data['notes'] ?? $payment->notes,
            ]);

            return $payment->fresh(['customer', 'receiver']);
        });
    }

    public function delete($payment): void
    {
        throw ValidationException::withMessages([
            'payment' => 'Payments are immutable records and cannot be deleted.',
        ]);
    }

    public function receive(Payment $payment, array $data = []): Payment
    {
        return DB::transaction(function () use ($payment, $data) {
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);

            if (!in_array($payment->status, [PaymentStatus::IntentCreated, PaymentStatus::WaitingPayment], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Only intent_created or waiting_payment payments may transition to received.',
                ]);
            }

            $payment->update([
                'status' => PaymentStatus::Received->value,
                'payment_date' => $data['payment_date'] ?? $payment->payment_date,
                'amount' => $data['amount'] ?? $payment->amount,
                'method' => $data['method'] ?? $payment->method,
                'channel_reference' => $data['channel_reference'] ?? $payment->channel_reference,
                'received_by' => $data['received_by'] ?? auth()->id(),
                'notes' => $data['notes'] ?? $payment->notes,
            ]);

            event(new PaymentReceived($payment->id, $payment->customer_id, (float) $payment->amount));

            return $payment->fresh(['customer', 'receiver', 'allocations']);
        });
    }

    public function validatePayment(Payment $payment): Payment
    {
        return DB::transaction(function () use ($payment) {
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);

            if (!$payment->isReceived()) {
                throw ValidationException::withMessages([
                    'status' => 'Only received payments may be validated.',
                ]);
            }

            $payment->update(['status' => PaymentStatus::Validated->value]);

            event(new PaymentValidated($payment->id, $payment->customer_id));

            return $payment->fresh(['customer', 'receiver', 'allocations']);
        });
    }

    public function record(Payment $payment): Payment
    {
        return DB::transaction(function () use ($payment) {
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);

            if (!$payment->isValidated()) {
                throw ValidationException::withMessages([
                    'status' => 'Only validated payments may be recorded.',
                ]);
            }

            $payment->update([
                'status' => PaymentStatus::Recorded->value,
                'recorded_at' => $payment->recorded_at ?? now(),
            ]);

            event(new PaymentRecorded($payment->id, $payment->customer_id));

            return $payment->fresh(['customer', 'receiver', 'allocations']);
        });
    }

    public function allocate(Payment $payment, array $allocations, ?string $notes = null): Payment
    {
        return DB::transaction(function () use ($payment, $allocations, $notes) {
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);

            if (!in_array($payment->status, [PaymentStatus::Recorded, PaymentStatus::PartiallyAllocated, PaymentStatus::FullyAllocated], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Only recorded/partially_allocated/fully_allocated payments may be allocated.',
                ]);
            }

            $remaining = $payment->unallocatedAmount();

            foreach ($allocations as $row) {
                $invoice = Invoice::lockForUpdate()->findOrFail($row['invoice_id']);
                $allocationAmount = (float) $row['allocated_amount'];

                if ($allocationAmount <= 0) {
                    throw ValidationException::withMessages([
                        'allocations' => 'Each allocation amount must be greater than zero.',
                    ]);
                }

                if ($allocationAmount > $remaining) {
                    throw ValidationException::withMessages([
                        'allocations' => 'Allocation amount exceeds remaining unallocated payment amount.',
                    ]);
                }

                $this->invoiceService->recordPaymentAllocation($invoice, $allocationAmount);

                PaymentAllocation::create([
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id,
                    'allocated_amount' => $allocationAmount,
                    'status' => PaymentAllocationStatus::Allocated->value,
                    'allocated_at' => now(),
                    'notes' => $row['notes'] ?? $notes,
                ]);

                $remaining -= $allocationAmount;
            }

            $allocatedAmount = (float) $payment->allocations()
                ->where('status', PaymentAllocationStatus::Allocated->value)
                ->sum('allocated_amount');

            if ($allocatedAmount <= 0) {
                throw ValidationException::withMessages([
                    'allocations' => 'At least one allocation is required.',
                ]);
            }

            $newStatus = $remaining > 0
                ? PaymentStatus::PartiallyAllocated->value
                : PaymentStatus::FullyAllocated->value;

            $payment->update(['status' => $newStatus]);

            if ($remaining > 0) {
                event(new PaymentPartiallyAllocated($payment->id, $payment->customer_id, $allocatedAmount, $remaining));
            } else {
                event(new PaymentFullyAllocated($payment->id, $payment->customer_id, $allocatedAmount));
            }

            return $payment->fresh(['customer', 'receiver', 'allocations.invoice']);
        });
    }

    public function complete(Payment $payment): Payment
    {
        return DB::transaction(function () use ($payment) {
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);

            if (!in_array($payment->status, [PaymentStatus::PartiallyAllocated, PaymentStatus::FullyAllocated], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Only partially_allocated or fully_allocated payments may be completed.',
                ]);
            }

            $payment->update([
                'status' => PaymentStatus::Completed->value,
                'completed_at' => $payment->completed_at ?? now(),
            ]);

            event(new PaymentCompleted($payment->id, $payment->customer_id));

            return $payment->fresh(['customer', 'receiver', 'allocations.invoice']);
        });
    }

    public function reverse(Payment $payment, string $reason): Payment
    {
        return DB::transaction(function () use ($payment, $reason) {
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);

            if (in_array($payment->status, [PaymentStatus::IntentCreated, PaymentStatus::WaitingPayment, PaymentStatus::Received, PaymentStatus::Validated, PaymentStatus::Reversed, PaymentStatus::Failed], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Only recorded/allocated/completed payments may be reversed.',
                ]);
            }

            $activeAllocations = $payment->allocations()
                ->where('status', PaymentAllocationStatus::Allocated->value)
                ->lockForUpdate()
                ->get();

            foreach ($activeAllocations as $allocation) {
                $allocation->update([
                    'status' => PaymentAllocationStatus::Reversed->value,
                    'reversed_at' => now(),
                    'reversal_reason' => $reason,
                ]);

                $this->invoiceService->reversePaymentAllocation($allocation->invoice, (float) $allocation->allocated_amount);
            }

            $payment->update([
                'status' => PaymentStatus::Reversed->value,
                'reversed_at' => now(),
                'reversal_reason' => $reason,
            ]);

            event(new PaymentReversed($payment->id, $payment->customer_id, $reason));

            return $payment->fresh(['customer', 'receiver', 'allocations.invoice']);
        });
    }

    public function fail(Payment $payment, string $reason): Payment
    {
        return DB::transaction(function () use ($payment, $reason) {
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);

            if ($payment->status->isTerminal()) {
                throw ValidationException::withMessages([
                    'status' => 'Terminal payments may not transition to failed.',
                ]);
            }

            $payment->update([
                'status' => PaymentStatus::Failed->value,
                'failure_reason' => $reason,
            ]);

            event(new PaymentFailed($payment->id, $payment->customer_id, $reason));

            return $payment->fresh(['customer', 'receiver', 'allocations.invoice']);
        });
    }

    public function buildIndexData(array $filters = [], int $perPage = 25): array
    {
        return [
            'payments' => $this->paginate($filters, $perPage),
            'statuses' => PaymentStatus::cases(),
            'filters' => $filters,
        ];
    }

    public function buildCreateData(): array
    {
        return [
            'statuses' => PaymentStatus::cases(),
        ];
    }

    public function buildEditData(Payment $payment): array
    {
        $payment->loadMissing(['customer', 'receiver', 'allocations.invoice']);

        return [
            'payment' => $payment,
            'statuses' => PaymentStatus::cases(),
        ];
    }

    public function findForShow(Payment $payment): array
    {
        $payment->loadMissing(['customer', 'receiver', 'allocations.invoice']);

        return [
            'payment' => $payment,
        ];
    }

    protected function applyDefaultRelationships(Builder $query): Builder
    {
        return $query->with(['customer', 'receiver']);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                    ->orWhere('channel_reference', 'like', "%{$search}%")
                    ->orWhereHas('customer', function (Builder $customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('customer_number', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        return $query;
    }

    protected function applyDefaultOrdering(Builder $query): Builder
    {
        return $query->orderByDesc('payment_date')->orderByDesc('id');
    }

    private function generatePaymentNumber(string $paymentDate): string
    {
        $prefix = 'PAY-' . date('Ym', strtotime($paymentDate)) . '-';
        $latest = Payment::lockForUpdate()
            ->where('payment_number', 'like', $prefix . '%')
            ->orderByDesc('payment_number')
            ->value('payment_number');

        if (!$latest) {
            return $prefix . '000001';
        }

        $sequence = (int) substr($latest, -6);

        return $prefix . str_pad((string) ($sequence + 1), 6, '0', STR_PAD_LEFT);
    }
}
