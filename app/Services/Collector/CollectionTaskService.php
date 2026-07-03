<?php

namespace App\Services\Collector;

use App\Domain\Events\CollectorAssigned;
use App\Domain\Events\CollectorVisitCompleted;
use App\Domain\Events\CollectorVisitStarted;
use App\Enums\CollectionTaskInvoiceStatus;
use App\Enums\CollectionTaskPaymentSubmissionStatus;
use App\Enums\CollectionTaskStatus;
use App\Enums\EmployeeStatus;
use App\Models\CollectionTask;
use App\Models\CollectionTaskInvoice;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Services\AbstractCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CollectionTaskService extends AbstractCrudService
{
    protected string $modelClass = CollectionTask::class;

    public function buildIndexData(array $filters = [], int $perPage = 25): array
    {
        return [
            'collectionTasks' => $this->paginate($filters, $perPage),
            'statuses' => CollectionTaskStatus::cases(),
        ];
    }

    public function buildCreateData(): array
    {
        return [
            'customers' => Customer::orderBy('name')->get(),
            'employees' => Employee::active()->orderBy('name')->get(),
            'statuses' => CollectionTaskStatus::cases(),
        ];
    }

    public function buildEditData(CollectionTask $collectionTask): array
    {
        return [
            'collectionTask' => $collectionTask->load(['customer', 'employee', 'invoices.invoice']),
            'customers' => Customer::orderBy('name')->get(),
            'employees' => Employee::orderBy('name')->get(),
            'statuses' => CollectionTaskStatus::cases(),
        ];
    }

    public function findForShow(CollectionTask $collectionTask): array
    {
        return [
            'collectionTask' => $collectionTask->load(['customer', 'employee', 'invoices.invoice']),
            'employees' => Employee::active()->orderBy('name')->get(),
        ];
    }

    public function create(array $data): CollectionTask
    {
        return DB::transaction(function () use ($data) {
            Customer::lockForUpdate()->findOrFail($data['customer_id']);

            $employeeId = $data['employee_id'] ?? null;

            if ($employeeId !== null) {
                $employee = Employee::lockForUpdate()->findOrFail($employeeId);

                if (!$employee->isActive()) {
                    throw ValidationException::withMessages([
                        'employee_id' => 'Only active employees may be assigned to collection tasks.',
                    ]);
                }
            }

            return CollectionTask::create([
                'customer_id' => $data['customer_id'],
                'employee_id' => $employeeId,
                'status' => $employeeId !== null
                    ? CollectionTaskStatus::Assigned->value
                    : CollectionTaskStatus::WaitingAssignment->value,
                'scheduled_for' => $data['scheduled_for'] ?? null,
                'payment_submission_status' => CollectionTaskPaymentSubmissionStatus::Pending->value,
                'notes' => $data['notes'] ?? null,
            ])->load(['customer', 'employee']);
        });
    }

    public function update(CollectionTask $collectionTask, array $data): CollectionTask
    {
        return DB::transaction(function () use ($collectionTask, $data) {
            $collectionTask = CollectionTask::lockForUpdate()->findOrFail($collectionTask->id);

            if ($collectionTask->isTerminal()) {
                throw ValidationException::withMessages([
                    'status' => 'Terminal collection tasks cannot be edited.',
                ]);
            }

            $collectionTask->update([
                'scheduled_for' => $data['scheduled_for'] ?? $collectionTask->scheduled_for,
                'notes' => $data['notes'] ?? $collectionTask->notes,
            ]);

            return $collectionTask->fresh(['customer', 'employee', 'invoices.invoice']);
        });
    }

    public function assign(CollectionTask $collectionTask, int $employeeId): CollectionTask
    {
        return DB::transaction(function () use ($collectionTask, $employeeId) {
            $collectionTask = CollectionTask::lockForUpdate()->findOrFail($collectionTask->id);
            $employee = Employee::lockForUpdate()->findOrFail($employeeId);

            if (!$employee->isActive()) {
                throw ValidationException::withMessages([
                    'employee_id' => 'Only active employees may be assigned to collection tasks.',
                ]);
            }

            $collectionTask->update([
                'employee_id' => $employee->id,
                'status' => CollectionTaskStatus::Assigned->value,
            ]);

            event(new CollectorAssigned($collectionTask->id, $collectionTask->customer_id, $employee->id));

            return $collectionTask->fresh(['customer', 'employee', 'invoices.invoice']);
        });
    }

    public function schedule(CollectionTask $collectionTask, array $data): CollectionTask
    {
        return DB::transaction(function () use ($collectionTask, $data) {
            $collectionTask = CollectionTask::lockForUpdate()->findOrFail($collectionTask->id);

            if (!$collectionTask->employee_id) {
                throw ValidationException::withMessages([
                    'employee_id' => 'A collector must be assigned before scheduling.',
                ]);
            }

            $collectionTask->update([
                'status' => CollectionTaskStatus::Scheduled->value,
                'scheduled_for' => $data['scheduled_for'] ?? $collectionTask->scheduled_for,
            ]);

            return $collectionTask->fresh(['customer', 'employee', 'invoices.invoice']);
        });
    }

    public function startRoute(CollectionTask $collectionTask): CollectionTask
    {
        return DB::transaction(function () use ($collectionTask) {
            $collectionTask = CollectionTask::lockForUpdate()->findOrFail($collectionTask->id);

            if (!$collectionTask->isScheduled()) {
                throw ValidationException::withMessages([
                    'status' => 'Only scheduled collection tasks may start route execution.',
                ]);
            }

            $collectionTask->update([
                'status' => CollectionTaskStatus::OnRoute->value,
                'route_started_at' => $collectionTask->route_started_at ?? now(),
            ]);

            event(new CollectorVisitStarted($collectionTask->id, $collectionTask->customer_id, (int) $collectionTask->employee_id));

            return $collectionTask->fresh(['customer', 'employee', 'invoices.invoice']);
        });
    }

    public function recordVisit(CollectionTask $collectionTask, array $data): CollectionTask
    {
        return DB::transaction(function () use ($collectionTask, $data) {
            $collectionTask = CollectionTask::lockForUpdate()->findOrFail($collectionTask->id);

            if (!$collectionTask->isOnRoute()) {
                throw ValidationException::withMessages([
                    'status' => 'Only on-route collection tasks may record a customer visit.',
                ]);
            }

            $collectionTask->update([
                'status' => CollectionTaskStatus::CustomerVisited->value,
                'visited_at' => $collectionTask->visited_at ?? now(),
                'payment_collected_amount' => $data['payment_collected_amount'] ?? $collectionTask->payment_collected_amount,
                'payment_submission_reference' => $data['payment_submission_reference'] ?? $collectionTask->payment_submission_reference,
                'payment_submission_status' => $data['payment_submission_reference'] ?? null
                    ? CollectionTaskPaymentSubmissionStatus::Submitted->value
                    : $collectionTask->payment_submission_status,
                'notes' => $data['notes'] ?? $collectionTask->notes,
            ]);

            event(new CollectorVisitCompleted(
                $collectionTask->id,
                $collectionTask->customer_id,
                (int) $collectionTask->employee_id,
                $collectionTask->payment_collected_amount !== null ? (float) $collectionTask->payment_collected_amount : null,
                $collectionTask->payment_submission_reference
            ));

            return $collectionTask->fresh(['customer', 'employee', 'invoices.invoice']);
        });
    }

    public function complete(CollectionTask $collectionTask): CollectionTask
    {
        return DB::transaction(function () use ($collectionTask) {
            $collectionTask = CollectionTask::lockForUpdate()->findOrFail($collectionTask->id);

            if (!$collectionTask->isCustomerVisited() && !$collectionTask->isFollowUpRequired()) {
                throw ValidationException::withMessages([
                    'status' => 'Only visited or follow-up tasks may be completed.',
                ]);
            }

            $collectionTask->update([
                'status' => CollectionTaskStatus::Completed->value,
                'completed_at' => $collectionTask->completed_at ?? now(),
            ]);

            return $collectionTask->fresh(['customer', 'employee', 'invoices.invoice']);
        });
    }

    public function followUpRequired(CollectionTask $collectionTask, string $reason): CollectionTask
    {
        return DB::transaction(function () use ($collectionTask, $reason) {
            $collectionTask = CollectionTask::lockForUpdate()->findOrFail($collectionTask->id);

            if (!$collectionTask->isOnRoute() && !$collectionTask->isCustomerVisited()) {
                throw ValidationException::withMessages([
                    'status' => 'Only active collection tasks may be marked for follow up.',
                ]);
            }

            $collectionTask->update([
                'status' => CollectionTaskStatus::FollowUpRequired->value,
                'follow_up_reason' => $reason,
                'completed_at' => $collectionTask->completed_at ?? now(),
            ]);

            return $collectionTask->fresh(['customer', 'employee', 'invoices.invoice']);
        });
    }

    public function cancel(CollectionTask $collectionTask, string $reason): CollectionTask
    {
        return DB::transaction(function () use ($collectionTask, $reason) {
            $collectionTask = CollectionTask::lockForUpdate()->findOrFail($collectionTask->id);

            if ($collectionTask->isTerminal()) {
                throw ValidationException::withMessages([
                    'status' => 'Terminal collection tasks cannot be cancelled again.',
                ]);
            }

            $collectionTask->update([
                'status' => CollectionTaskStatus::Cancelled->value,
                'cancellation_reason' => $reason,
                'completed_at' => $collectionTask->completed_at ?? now(),
            ]);

            return $collectionTask->fresh(['customer', 'employee', 'invoices.invoice']);
        });
    }

    public function addInvoice(CollectionTask $collectionTask, array $data): CollectionTaskInvoice
    {
        return DB::transaction(function () use ($collectionTask, $data) {
            $collectionTask = CollectionTask::lockForUpdate()->findOrFail($collectionTask->id);
            Invoice::lockForUpdate()->findOrFail($data['invoice_id']);

            return CollectionTaskInvoice::create([
                'collection_task_id' => $collectionTask->id,
                'invoice_id' => $data['invoice_id'],
                'status' => CollectionTaskInvoiceStatus::Created->value,
                'inclusion_reason' => $data['inclusion_reason'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    public function resolveInvoice(CollectionTaskInvoice $collectionTaskInvoice, array $data): CollectionTaskInvoice
    {
        return DB::transaction(function () use ($collectionTaskInvoice, $data) {
            $collectionTaskInvoice = CollectionTaskInvoice::lockForUpdate()->findOrFail($collectionTaskInvoice->id);

            $collectionTaskInvoice->update([
                'status' => CollectionTaskInvoiceStatus::Resolved->value,
                'resolution_outcome' => $data['resolution_outcome'] ?? $collectionTaskInvoice->resolution_outcome,
                'resolved_at' => $collectionTaskInvoice->resolved_at ?? now(),
                'notes' => $data['notes'] ?? $collectionTaskInvoice->notes,
            ]);

            return $collectionTaskInvoice->fresh(['collectionTask.customer', 'invoice']);
        });
    }

    public function cancelInvoice(CollectionTaskInvoice $collectionTaskInvoice, ?string $notes = null): CollectionTaskInvoice
    {
        return DB::transaction(function () use ($collectionTaskInvoice, $notes) {
            $collectionTaskInvoice = CollectionTaskInvoice::lockForUpdate()->findOrFail($collectionTaskInvoice->id);

            $collectionTaskInvoice->update([
                'status' => CollectionTaskInvoiceStatus::Cancelled->value,
                'cancelled_at' => $collectionTaskInvoice->cancelled_at ?? now(),
                'notes' => $notes ?? $collectionTaskInvoice->notes,
            ]);

            return $collectionTaskInvoice->fresh(['collectionTask.customer', 'invoice']);
        });
    }

    protected function applyDefaultRelationships(Builder $query): Builder
    {
        return $query->with(['customer', 'employee', 'invoices.invoice']);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->whereHas('customer', function (Builder $builder) use ($search) {
                $builder->where('customer_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        return $query;
    }

    protected function applyDefaultOrdering(Builder $query): Builder
    {
        return $query->latest('scheduled_for')->latest('id');
    }
}