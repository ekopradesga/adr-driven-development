<?php

namespace App\Services\Billing;

use App\Domain\Events\InvoiceCancelled;
use App\Domain\Events\InvoiceGenerated;
use App\Domain\Events\InvoiceOverdue;
use App\Domain\Events\InvoicePublished;
use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Subscription;
use App\Services\AbstractCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * InvoiceService — Billing module.
 *
 * Owns all invoice business logic, lifecycle transitions, and event dispatch.
 * Controllers delegate to this service and remain thin orchestrators.
 */
class InvoiceService extends AbstractCrudService
{
    protected string $modelClass = Invoice::class;

    // ---------------------------------------------------------------------
    // CRUD
    // ---------------------------------------------------------------------

    public function create(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $subscription = Subscription::lockForUpdate()->findOrFail($data['subscription_id']);
            $customer = Customer::lockForUpdate()->findOrFail($data['customer_id']);

            if (!$subscription->isActive()) {
                throw ValidationException::withMessages([
                    'subscription_id' => 'Invoice generation requires an active subscription.',
                ]);
            }

            if ($subscription->customer_id !== $customer->id) {
                throw ValidationException::withMessages([
                    'customer_id' => 'Customer and subscription must belong together.',
                ]);
            }

            $this->assertPeriodUnique($subscription->id, $data['period_start'], $data['period_end']);

            $invoice = Invoice::create([
                'invoice_number'     => $this->generateInvoiceNumber($data['issue_date'] ?? now()->toDateString()),
                'customer_id'        => $customer->id,
                'subscription_id'    => $subscription->id,
                'status'             => InvoiceStatus::Draft->value,
                'period_start'       => $data['period_start'],
                'period_end'         => $data['period_end'],
                'issue_date'         => $data['issue_date'],
                'due_date'           => $data['due_date'],
                'subtotal_amount'    => $data['subtotal_amount'] ?? 0,
                'tax_amount'         => $data['tax_amount'] ?? 0,
                'discount_amount'    => $data['discount_amount'] ?? 0,
                'total_amount'       => $data['total_amount'] ?? (($data['subtotal_amount'] ?? 0) + ($data['tax_amount'] ?? 0) - ($data['discount_amount'] ?? 0)),
                'paid_amount'        => 0,
                'balance_amount'     => $data['total_amount'] ?? (($data['subtotal_amount'] ?? 0) + ($data['tax_amount'] ?? 0) - ($data['discount_amount'] ?? 0)),
                'notes'              => $data['notes'] ?? null,
            ]);

            foreach (($data['items'] ?? []) as $index => $item) {
                $invoice->items()->create([
                    'description'  => $item['description'],
                    'item_type'    => $item['item_type'] ?? 'subscription',
                    'quantity'     => $item['quantity'] ?? 1,
                    'unit_price'   => $item['unit_price'],
                    'total_amount' => ($item['quantity'] ?? 1) * $item['unit_price'],
                    'sort_order'   => $item['sort_order'] ?? $index,
                    'notes'        => $item['notes'] ?? null,
                ]);
            }

            event(new InvoiceGenerated($invoice->id, $customer->id, $subscription->id, auth()->id()));

            return $invoice->fresh(['customer', 'subscription', 'items']);
        });
    }

    public function update($invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            $invoice = Invoice::lockForUpdate()->findOrFail($invoice->id);

            if (!$invoice->isDraft()) {
                throw ValidationException::withMessages([
                    'status' => 'Only draft invoices may be edited.',
                ]);
            }

            $invoice->update([
                'period_start'    => $data['period_start'] ?? $invoice->period_start,
                'period_end'      => $data['period_end'] ?? $invoice->period_end,
                'issue_date'      => $data['issue_date'] ?? $invoice->issue_date,
                'due_date'        => $data['due_date'] ?? $invoice->due_date,
                'tax_amount'      => $data['tax_amount'] ?? $invoice->tax_amount,
                'discount_amount' => $data['discount_amount'] ?? $invoice->discount_amount,
                'notes'           => $data['notes'] ?? $invoice->notes,
            ]);

            return $invoice->fresh(['customer', 'subscription', 'items']);
        });
    }

    public function delete($invoice): void
    {
        $this->cancel($invoice, 'Cancelled through destroy action.');
    }

    // ---------------------------------------------------------------------
    // Lifecycle
    // ---------------------------------------------------------------------

    public function publish(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice) {
            $invoice = Invoice::lockForUpdate()->findOrFail($invoice->id);

            if (!$invoice->isDraft()) {
                throw ValidationException::withMessages([
                    'status' => 'Only draft invoices may be published.',
                ]);
            }

            if ($invoice->items()->count() === 0) {
                throw ValidationException::withMessages([
                    'items' => 'Invoice must contain at least one invoice item before publication.',
                ]);
            }

            if ($invoice->total_amount <= 0) {
                throw ValidationException::withMessages([
                    'total_amount' => 'Invoice total must be positive before publication.',
                ]);
            }

            $invoice->update([
                'status'       => InvoiceStatus::Published->value,
                'published_at' => $invoice->published_at ?? now(),
            ]);

            event(new InvoicePublished(
                $invoice->id,
                $invoice->customer_id,
                $invoice->subscription_id,
                (float) $invoice->total_amount,
                auth()->id()
            ));

            return $invoice->fresh(['customer', 'subscription', 'items']);
        });
    }

    public function cancel(Invoice $invoice, string $reason): Invoice
    {
        return DB::transaction(function () use ($invoice, $reason) {
            $invoice = Invoice::lockForUpdate()->findOrFail($invoice->id);

            if (!$invoice->isDraft()) {
                throw ValidationException::withMessages([
                    'status' => 'Only draft invoices may be cancelled.',
                ]);
            }

            $invoice->update([
                'status'               => InvoiceStatus::Cancelled->value,
                'cancelled_at'         => now(),
                'cancellation_reason'  => $reason,
            ]);

            event(new InvoiceCancelled(
                $invoice->id,
                $invoice->customer_id,
                $invoice->subscription_id,
                $reason,
                auth()->id()
            ));

            return $invoice->fresh(['customer', 'subscription', 'items']);
        });
    }

    public function markOverdue(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice) {
            $invoice = Invoice::lockForUpdate()->findOrFail($invoice->id);

            if (!in_array($invoice->status, [InvoiceStatus::Published, InvoiceStatus::PartiallyPaid], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Only published or partially paid invoices may become overdue.',
                ]);
            }

            $invoice->update([
                'status'     => InvoiceStatus::Overdue->value,
                'overdue_at' => $invoice->overdue_at ?? now(),
            ]);

            event(new InvoiceOverdue(
                $invoice->id,
                $invoice->customer_id,
                $invoice->subscription_id,
                (float) $invoice->balance_amount
            ));

            return $invoice->fresh(['customer', 'subscription', 'items']);
        });
    }

    public function recordPaymentAllocation(Invoice $invoice, float $amount): Invoice
    {
        return DB::transaction(function () use ($invoice, $amount) {
            $invoice = Invoice::lockForUpdate()->findOrFail($invoice->id);

            if (!$invoice->isPayable() && !$invoice->isPartiallyPaid()) {
                throw ValidationException::withMessages([
                    'status' => 'Payment allocations may only be recorded against payable invoices.',
                ]);
            }

            $paidAmount = (float) $invoice->paid_amount + $amount;
            $balanceAmount = max(0, (float) $invoice->total_amount - $paidAmount);
            $status = $balanceAmount <= 0 ? InvoiceStatus::Paid->value : InvoiceStatus::PartiallyPaid->value;

            $invoice->update([
                'paid_amount'    => $paidAmount,
                'balance_amount' => $balanceAmount,
                'status'         => $status,
            ]);

            return $invoice->fresh(['customer', 'subscription', 'items']);
        });
    }

    // ---------------------------------------------------------------------
    // View Builders
    // ---------------------------------------------------------------------

    public function buildIndexData(array $filters = [], int $perPage = 25): array
    {
        return [
            'invoices' => $this->paginate($filters, $perPage),
            'statuses' => InvoiceStatus::cases(),
            'filters'  => $filters,
        ];
    }

    public function buildCreateData(): array
    {
        return [
            'statuses' => InvoiceStatus::cases(),
        ];
    }

    public function buildEditData(Invoice $invoice): array
    {
        $invoice->loadMissing(['customer', 'subscription', 'items']);

        return [
            'invoice'  => $invoice,
            'statuses' => InvoiceStatus::cases(),
        ];
    }

    public function findForShow(Invoice $invoice): array
    {
        $invoice->loadMissing(['customer', 'subscription', 'items']);

        return [
            'invoice' => $invoice,
        ];
    }

    // ---------------------------------------------------------------------
    // AbstractCrudService Hooks
    // ---------------------------------------------------------------------

    protected function applyDefaultRelationships(Builder $query): Builder
    {
        return $query->with(['customer', 'subscription']);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
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
        return $query->orderByDesc('issue_date')->orderByDesc('id');
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    private function generateInvoiceNumber(string $issueDate): string
    {
        $prefix = 'INV-' . date('Ym', strtotime($issueDate)) . '-';
        $latest = Invoice::lockForUpdate()
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        if (!$latest) {
            return $prefix . '000001';
        }

        $sequence = (int) substr($latest, -6);

        return $prefix . str_pad((string) ($sequence + 1), 6, '0', STR_PAD_LEFT);
    }

    private function assertPeriodUnique(int $subscriptionId, string $periodStart, string $periodEnd): void
    {
        $exists = Invoice::where('subscription_id', $subscriptionId)
            ->whereDate('period_start', $periodStart)
            ->whereDate('period_end', $periodEnd)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'subscription_id' => 'An invoice already exists for this subscription and billing period.',
            ]);
        }
    }
}
