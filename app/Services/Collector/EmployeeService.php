<?php

namespace App\Services\Collector;

use App\Enums\EmployeeStatus;
use App\Models\Employee;
use App\Services\AbstractCrudService;
use Illuminate\Database\Eloquent\Builder;

class EmployeeService extends AbstractCrudService
{
    protected string $modelClass = Employee::class;

    public function buildIndexData(array $filters = [], int $perPage = 25): array
    {
        return [
            'employees' => $this->paginate($filters, $perPage),
            'statuses' => EmployeeStatus::cases(),
        ];
    }

    public function buildCreateData(): array
    {
        return [
            'statuses' => EmployeeStatus::cases(),
        ];
    }

    public function buildEditData(Employee $employee): array
    {
        return [
            'employee' => $employee,
            'statuses' => EmployeeStatus::cases(),
        ];
    }

    public function findForShow(Employee $employee): array
    {
        return [
            'employee' => $employee->load(['user', 'collectionTasks.customer', 'collectionTasks.invoices.invoice']),
        ];
    }

    protected function applyDefaultRelationships(Builder $query): Builder
    {
        return $query->with(['user']);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function (Builder $builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    protected function applyDefaultOrdering(Builder $query): Builder
    {
        return $query->orderBy('name');
    }
}