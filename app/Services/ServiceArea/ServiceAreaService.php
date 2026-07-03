<?php

namespace App\Services\ServiceArea;

use App\Domain\Events\ServiceAreaActivated;
use App\Domain\Events\ServiceAreaArchived;
use App\Domain\Events\ServiceAreaCreated;
use App\Domain\Events\ServiceAreaMerged;
use App\Enums\ClusterStatus;
use App\Enums\EmployeeStatus;
use App\Enums\ServiceAreaLevel;
use App\Enums\ServiceAreaStatus;
use App\Models\Cluster;
use App\Models\Employee;
use App\Models\ServiceArea;
use App\Services\AbstractCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServiceAreaService extends AbstractCrudService
{
    protected string $modelClass = ServiceArea::class;

    public function buildIndexData(array $filters = [], int $perPage = 25): array
    {
        return [
            'serviceAreas' => $this->paginate($filters, $perPage),
            'clusters' => Cluster::orderBy('name')->get(),
            'statuses' => ServiceAreaStatus::cases(),
            'levels' => ServiceAreaLevel::cases(),
        ];
    }

    public function buildCreateData(): array
    {
        return [
            'clusters' => Cluster::active()->orderBy('name')->get(),
            'parentOptions' => ServiceArea::active()->orderBy('name')->get(),
            'statuses' => ServiceAreaStatus::cases(),
            'levels' => ServiceAreaLevel::cases(),
        ];
    }

    public function buildEditData(ServiceArea $serviceArea): array
    {
        return [
            'serviceArea' => $serviceArea->load(['cluster', 'parent']),
            'clusters' => Cluster::orderBy('name')->get(),
            'parentOptions' => ServiceArea::whereKeyNot($serviceArea->id)->orderBy('name')->get(),
            'statuses' => ServiceAreaStatus::cases(),
            'levels' => ServiceAreaLevel::cases(),
        ];
    }

    public function findForShow(ServiceArea $serviceArea): array
    {
        return [
            'serviceArea' => $serviceArea->load(['cluster', 'parent', 'children', 'customers', 'employees.user', 'mergedInto']),
            'employees' => Employee::active()->orderBy('name')->get(),
            'mergeTargets' => ServiceArea::active()->whereKeyNot($serviceArea->id)->orderBy('name')->get(),
        ];
    }

    public function create(array $data): ServiceArea
    {
        return DB::transaction(function () use ($data) {
            $cluster = Cluster::lockForUpdate()->findOrFail($data['cluster_id']);

            if (!$cluster->isActive() && ($data['status'] ?? ServiceAreaStatus::Draft->value) === ServiceAreaStatus::Active->value) {
                throw ValidationException::withMessages([
                    'cluster_id' => 'Active service areas require an active cluster.',
                ]);
            }

            $serviceArea = ServiceArea::create([
                'cluster_id' => $cluster->id,
                'parent_id' => $data['parent_id'] ?? null,
                'name' => $data['name'],
                'code' => $data['code'],
                'level' => $data['level'],
                'boundary_geojson' => $data['boundary_geojson'] ?? null,
                'center_latitude' => $data['center_latitude'] ?? null,
                'center_longitude' => $data['center_longitude'] ?? null,
                'status' => $data['status'] ?? ServiceAreaStatus::Draft->value,
                'notes' => $data['notes'] ?? null,
            ]);

            event(new ServiceAreaCreated($serviceArea->id, $cluster->id, auth()->id()));

            return $serviceArea->fresh();
        });
    }

    public function update($serviceArea, array $data): ServiceArea
    {
        return DB::transaction(function () use ($serviceArea, $data) {
            $serviceArea = ServiceArea::lockForUpdate()->findOrFail($serviceArea->id);

            if ($serviceArea->status->isTerminal()) {
                throw ValidationException::withMessages([
                    'status' => 'Merged or archived service areas cannot be edited.',
                ]);
            }

            $cluster = Cluster::lockForUpdate()->findOrFail($data['cluster_id']);

            $serviceArea->update([
                'cluster_id' => $cluster->id,
                'parent_id' => $data['parent_id'] ?? null,
                'name' => $data['name'],
                'code' => $data['code'],
                'level' => $data['level'],
                'boundary_geojson' => $data['boundary_geojson'] ?? null,
                'center_latitude' => $data['center_latitude'] ?? null,
                'center_longitude' => $data['center_longitude'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->assertAcyclic($serviceArea);

            return $serviceArea->fresh();
        });
    }

    public function activate(ServiceArea $serviceArea): ServiceArea
    {
        return DB::transaction(function () use ($serviceArea) {
            $serviceArea = ServiceArea::lockForUpdate()->findOrFail($serviceArea->id);
            $cluster = Cluster::lockForUpdate()->findOrFail($serviceArea->cluster_id);

            if (!$cluster->isActive()) {
                throw ValidationException::withMessages([
                    'cluster' => 'Only service areas in active clusters may be activated.',
                ]);
            }

            $serviceArea->update(['status' => ServiceAreaStatus::Active->value]);

            event(new ServiceAreaActivated($serviceArea->id, $cluster->id, auth()->id()));

            return $serviceArea->fresh();
        });
    }

    public function archive(ServiceArea $serviceArea): ServiceArea
    {
        return DB::transaction(function () use ($serviceArea) {
            $serviceArea = ServiceArea::lockForUpdate()->findOrFail($serviceArea->id);

            if ($serviceArea->customers()->exists()) {
                throw ValidationException::withMessages([
                    'service_area' => 'Service areas with assigned customers cannot be archived.',
                ]);
            }

            if ($serviceArea->employees()->exists()) {
                throw ValidationException::withMessages([
                    'service_area' => 'Service areas with assigned employees cannot be archived.',
                ]);
            }

            $serviceArea->update([
                'status' => ServiceAreaStatus::Archived->value,
                'archived_at' => $serviceArea->archived_at ?? now(),
            ]);

            event(new ServiceAreaArchived($serviceArea->id, auth()->id()));

            return $serviceArea->fresh();
        });
    }

    public function merge(ServiceArea $serviceArea, int $destinationServiceAreaId): ServiceArea
    {
        return DB::transaction(function () use ($serviceArea, $destinationServiceAreaId) {
            $serviceArea = ServiceArea::lockForUpdate()->findOrFail($serviceArea->id);
            $destination = ServiceArea::lockForUpdate()->findOrFail($destinationServiceAreaId);

            if ($serviceArea->id === $destination->id) {
                throw ValidationException::withMessages([
                    'merged_into_service_area_id' => 'Service area cannot be merged into itself.',
                ]);
            }

            if (!$destination->isActive()) {
                throw ValidationException::withMessages([
                    'merged_into_service_area_id' => 'Merge destination must be active.',
                ]);
            }

            if ($serviceArea->customers()->exists()) {
                throw ValidationException::withMessages([
                    'service_area' => 'Reassign customers before merging this service area.',
                ]);
            }

            if ($serviceArea->employees()->exists()) {
                throw ValidationException::withMessages([
                    'service_area' => 'Reassign employee assignments before merging this service area.',
                ]);
            }

            $serviceArea->update([
                'status' => ServiceAreaStatus::Merged->value,
                'merged_into_service_area_id' => $destination->id,
                'merged_at' => $serviceArea->merged_at ?? now(),
            ]);

            event(new ServiceAreaMerged($serviceArea->id, $destination->id, auth()->id()));

            return $serviceArea->fresh();
        });
    }

    public function assignEmployee(ServiceArea $serviceArea, int $employeeId, bool $isPrimary = false): ServiceArea
    {
        return DB::transaction(function () use ($serviceArea, $employeeId, $isPrimary) {
            $serviceArea = ServiceArea::lockForUpdate()->findOrFail($serviceArea->id);
            $employee = Employee::lockForUpdate()->findOrFail($employeeId);

            if (!$serviceArea->isActive()) {
                throw ValidationException::withMessages([
                    'service_area' => 'Only active service areas may receive employee assignments.',
                ]);
            }

            if (!$employee->isActive()) {
                throw ValidationException::withMessages([
                    'employee_id' => 'Only active employees may be assigned to a service area.',
                ]);
            }

            if ($isPrimary) {
                DB::table('employee_service_area')
                    ->where('employee_id', $employee->id)
                    ->update(['is_primary' => false, 'updated_at' => now()]);
            }

            $serviceArea->employees()->syncWithoutDetaching([
                $employee->id => [
                    'is_primary' => $isPrimary,
                    'assigned_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            return $serviceArea->fresh(['employees']);
        });
    }

    public function removeEmployee(ServiceArea $serviceArea, int $employeeId): ServiceArea
    {
        return DB::transaction(function () use ($serviceArea, $employeeId) {
            $serviceArea = ServiceArea::lockForUpdate()->findOrFail($serviceArea->id);
            $serviceArea->employees()->detach($employeeId);

            return $serviceArea->fresh(['employees']);
        });
    }

    protected function applyDefaultRelationships(Builder $query): Builder
    {
        return $query->with(['cluster', 'parent'])->withCount(['customers', 'employees', 'children']);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['cluster_id'])) {
            $query->where('cluster_id', $filters['cluster_id']);
        }

        if (!empty($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        return $query;
    }

    protected function applyDefaultOrdering(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    private function assertAcyclic(ServiceArea $serviceArea): void
    {
        $seen = [$serviceArea->id];
        $current = $serviceArea->parent;

        while ($current) {
            if (in_array($current->id, $seen, true)) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Service area hierarchy must remain acyclic.',
                ]);
            }

            $seen[] = $current->id;
            $current = $current->parent;
        }
    }
}