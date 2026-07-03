<?php

namespace App\Services\Network;

use App\Domain\Events\OltActivated;
use App\Domain\Events\OltCreated;
use App\Domain\Events\OltMaintenanceStarted;
use App\Domain\Events\OltRetired;
use App\Enums\OltStatus;
use App\Models\Olt;
use App\Services\AbstractCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OltService extends AbstractCrudService
{
    protected string $modelClass = Olt::class;

    public function buildIndexData(array $filters = [], int $perPage = 25): array
    {
        return [
            'olts' => $this->paginate($filters, $perPage),
            'statuses' => OltStatus::cases(),
        ];
    }

    public function buildCreateData(): array
    {
        return [];
    }

    public function buildEditData(Olt $olt): array
    {
        return [
            'olt' => $olt,
        ];
    }

    public function findForShow(Olt $olt): array
    {
        return [
            'olt' => $olt->load([
                'onus' => fn ($query) => $query->latest('id')->limit(10),
            ]),
        ];
    }

    public function create(array $data): Olt
    {
        return DB::transaction(function () use ($data) {
            $olt = Olt::create([
                'olt_code' => $data['olt_code'],
                'name' => $data['name'],
                'vendor' => $data['vendor'] ?? null,
                'model' => $data['model'] ?? null,
                'ip_address' => $data['ip_address'],
                'snmp_community' => $data['snmp_community'] ?? null,
                'api_username' => $data['api_username'] ?? null,
                'api_password' => $data['api_password'] ?? null,
                'location_name' => $data['location_name'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'status' => OltStatus::Planned->value,
                'last_seen_at' => $data['last_seen_at'] ?? null,
                'created_by' => auth()->id(),
            ]);

            event(new OltCreated($olt->id, auth()->id()));

            return $olt->fresh();
        });
    }

    public function update($olt, array $data): Olt
    {
        return DB::transaction(function () use ($olt, $data) {
            $olt = Olt::lockForUpdate()->findOrFail($olt->id);

            if ($olt->isRetired()) {
                throw ValidationException::withMessages([
                    'status' => 'Retired OLT assets cannot be edited.',
                ]);
            }

            $olt->update([
                'olt_code' => $data['olt_code'],
                'name' => $data['name'],
                'vendor' => $data['vendor'] ?? null,
                'model' => $data['model'] ?? null,
                'ip_address' => $data['ip_address'],
                'snmp_community' => $data['snmp_community'] ?? null,
                'api_username' => $data['api_username'] ?? null,
                'api_password' => $data['api_password'] ?? null,
                'location_name' => $data['location_name'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'last_seen_at' => $data['last_seen_at'] ?? null,
                'updated_by' => auth()->id(),
            ]);

            return $olt->fresh();
        });
    }

    public function activate(Olt $olt): Olt
    {
        return DB::transaction(function () use ($olt) {
            $olt = Olt::lockForUpdate()->findOrFail($olt->id);

            if ($olt->isRetired()) {
                throw ValidationException::withMessages([
                    'status' => 'Retired OLT assets cannot be activated.',
                ]);
            }

            if ($olt->isActive()) {
                return $olt;
            }

            $olt->update([
                'status' => OltStatus::Active->value,
                'updated_by' => auth()->id(),
            ]);

            event(new OltActivated($olt->id, auth()->id()));

            return $olt->fresh();
        });
    }

    public function markMaintenance(Olt $olt): Olt
    {
        return DB::transaction(function () use ($olt) {
            $olt = Olt::lockForUpdate()->findOrFail($olt->id);

            if (!$olt->isActive()) {
                throw ValidationException::withMessages([
                    'status' => 'Only active OLT assets can enter maintenance.',
                ]);
            }

            $olt->update([
                'status' => OltStatus::Maintenance->value,
                'updated_by' => auth()->id(),
            ]);

            event(new OltMaintenanceStarted($olt->id, auth()->id()));

            return $olt->fresh();
        });
    }

    public function retire(Olt $olt): Olt
    {
        return DB::transaction(function () use ($olt) {
            $olt = Olt::lockForUpdate()->findOrFail($olt->id);

            if ($olt->isRetired()) {
                return $olt;
            }

            $activeOnus = $olt->onus()->whereNull('deleted_at')->whereIn('status', ['active', 'offline', 'suspended'])->count();
            if ($activeOnus > 0) {
                throw ValidationException::withMessages([
                    'olt' => "OLT cannot be retired while {$activeOnus} ONU record(s) still depend on it.",
                ]);
            }

            $olt->update([
                'status' => OltStatus::Retired->value,
                'updated_by' => auth()->id(),
            ]);

            event(new OltRetired($olt->id, auth()->id()));

            return $olt->fresh();
        });
    }

    protected function applyDefaultRelationships(Builder $query): Builder
    {
        return $query->withCount('onus');
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('olt_code', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
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
