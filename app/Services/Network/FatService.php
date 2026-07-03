<?php

namespace App\Services\Network;

use App\Domain\Events\FatActivated;
use App\Domain\Events\FatCreated;
use App\Domain\Events\FatMaintenanceStarted;
use App\Domain\Events\FatRetired;
use App\Enums\FatStatus;
use App\Models\Fat;
use App\Models\Odf;
use App\Models\ServiceArea;
use App\Services\AbstractCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FatService extends AbstractCrudService
{
    protected string $modelClass = Fat::class;

    public function buildIndexData(array $filters = [], int $perPage = 25): array
    {
        $fats = $this->paginate($filters, $perPage);
        $fats->setCollection(
            $fats->getCollection()->map(function (Fat $fat) {
                $fat->setAttribute('health_summary', $this->summarizeHealth($fat));
                return $fat;
            })
        );

        return [
            'fats' => $fats,
            'statuses' => FatStatus::cases(),
        ];
    }

    public function buildCreateData(): array
    {
        return [
            'odfs' => Odf::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'odf_code']),
            'serviceAreas' => ServiceArea::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'code']),
        ];
    }

    public function buildEditData(Fat $fat): array
    {
        return [
            'fat' => $fat,
            'odfs' => Odf::query()->whereIn('status', ['installed', 'active'])->orderBy('name')->get(['id', 'name', 'odf_code']),
            'serviceAreas' => ServiceArea::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'code']),
        ];
    }

    public function findForShow(Fat $fat): array
    {
        $fat->load(['odf.olt', 'serviceArea', 'onus']);

        return [
            'fat' => $fat,
            'healthSummary' => $this->summarizeHealth($fat),
        ];
    }

    public function create(array $data): Fat
    {
        return DB::transaction(function () use ($data) {
            $fat = Fat::create([
                'odf_id' => $data['odf_id'],
                'service_area_id' => $data['service_area_id'] ?? null,
                'fat_code' => $data['fat_code'],
                'name' => $data['name'],
                'capacity_ports' => $data['capacity_ports'],
                'used_ports' => $data['used_ports'] ?? 0,
                'splitter_ratio' => $data['splitter_ratio'] ?? null,
                'location_name' => $data['location_name'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'status' => FatStatus::Planned->value,
                'last_onu_ping_at' => $data['last_onu_ping_at'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            event(new FatCreated($fat->id, auth()->id()));

            return $fat->fresh();
        });
    }

    public function update($fat, array $data): Fat
    {
        return DB::transaction(function () use ($fat, $data) {
            $fat = Fat::lockForUpdate()->findOrFail($fat->id);

            if ($fat->isRetired()) {
                throw ValidationException::withMessages([
                    'status' => 'Retired FAT assets cannot be edited.',
                ]);
            }

            $fat->update([
                'odf_id' => $data['odf_id'],
                'service_area_id' => $data['service_area_id'] ?? null,
                'fat_code' => $data['fat_code'],
                'name' => $data['name'],
                'capacity_ports' => $data['capacity_ports'],
                'used_ports' => $data['used_ports'] ?? 0,
                'splitter_ratio' => $data['splitter_ratio'] ?? null,
                'location_name' => $data['location_name'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'last_onu_ping_at' => $data['last_onu_ping_at'] ?? null,
                'notes' => $data['notes'] ?? null,
                'updated_by' => auth()->id(),
            ]);

            return $fat->fresh();
        });
    }

    public function activate(Fat $fat): Fat
    {
        return DB::transaction(function () use ($fat) {
            $fat = Fat::lockForUpdate()->findOrFail($fat->id);

            if ($fat->isRetired()) {
                throw ValidationException::withMessages([
                    'status' => 'Retired FAT assets cannot be activated.',
                ]);
            }

            if ($fat->isActive()) {
                return $fat;
            }

            $fat->update([
                'status' => FatStatus::Active->value,
                'updated_by' => auth()->id(),
            ]);

            event(new FatActivated($fat->id, auth()->id()));

            return $fat->fresh();
        });
    }

    public function markMaintenance(Fat $fat): Fat
    {
        return DB::transaction(function () use ($fat) {
            $fat = Fat::lockForUpdate()->findOrFail($fat->id);

            if (!$fat->isActive()) {
                throw ValidationException::withMessages([
                    'status' => 'Only active FAT assets can enter maintenance.',
                ]);
            }

            $fat->update([
                'status' => FatStatus::Maintenance->value,
                'updated_by' => auth()->id(),
            ]);

            event(new FatMaintenanceStarted($fat->id, auth()->id()));

            return $fat->fresh();
        });
    }

    public function retire(Fat $fat): Fat
    {
        return DB::transaction(function () use ($fat) {
            $fat = Fat::lockForUpdate()->findOrFail($fat->id);

            if ($fat->isRetired()) {
                return $fat;
            }

            $dependentOnus = $fat->onus()->whereNull('deleted_at')->whereIn('status', ['active', 'offline', 'suspended'])->count();
            if ($dependentOnus > 0) {
                throw ValidationException::withMessages([
                    'fat' => "FAT cannot be retired while {$dependentOnus} ONU record(s) still depend on it.",
                ]);
            }

            $fat->update([
                'status' => FatStatus::Retired->value,
                'updated_by' => auth()->id(),
            ]);

            event(new FatRetired($fat->id, auth()->id()));

            return $fat->fresh();
        });
    }

    public function summarizeHealth(Fat $fat): array
    {
        $fat->loadMissing('onus.olt');

        $onus = $fat->onus;
        if ($onus->isEmpty()) {
            return ['label' => 'Unknown', 'badge' => 'secondary', 'reason' => 'No downstream ONU mapped.'];
        }

        $parentOltReachable = $onus->contains(function ($onu) {
            return $onu->olt && $onu->olt->isActive();
        });

        $reachable = $onus->where('status', 'active')->count();
        $unreachable = $onus->whereIn('status', ['offline', 'suspended'])->count();

        if (!$parentOltReachable) {
            return ['label' => 'Unknown', 'badge' => 'secondary', 'reason' => 'Parent OLT is not operationally reachable.'];
        }

        if ($reachable === $onus->count()) {
            return ['label' => 'Healthy', 'badge' => 'success', 'reason' => 'All downstream ONU endpoints are reachable.'];
        }

        if ($reachable === 0 && $unreachable > 0) {
            return ['label' => 'Critical', 'badge' => 'danger', 'reason' => 'All downstream ONU endpoints are unreachable under a reachable parent OLT.'];
        }

        return ['label' => 'Warning', 'badge' => 'warning', 'reason' => 'Partial downstream ONU reachability loss detected.'];
    }

    protected function applyDefaultRelationships(Builder $query): Builder
    {
        return $query->with(['odf.olt', 'serviceArea'])->withCount('onus');
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('fat_code', 'like', "%{$search}%")
                    ->orWhere('location_name', 'like', "%{$search}%");
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
