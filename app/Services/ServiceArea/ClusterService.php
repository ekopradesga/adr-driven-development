<?php

namespace App\Services\ServiceArea;

use App\Domain\Events\ClusterActivated;
use App\Domain\Events\ClusterCreated;
use App\Domain\Events\ClusterInactivated;
use App\Enums\ClusterStatus;
use App\Enums\CustomerStatus;
use App\Enums\ServiceAreaStatus;
use App\Models\Cluster;
use App\Services\AbstractCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClusterService extends AbstractCrudService
{
    protected string $modelClass = Cluster::class;

    public function buildIndexData(array $filters = [], int $perPage = 25): array
    {
        return [
            'clusters' => $this->paginate($filters, $perPage),
            'statuses' => ClusterStatus::cases(),
        ];
    }

    public function buildCreateData(): array
    {
        return [];
    }

    public function buildEditData(Cluster $cluster): array
    {
        return [
            'cluster' => $cluster,
        ];
    }

    public function findForShow(Cluster $cluster): array
    {
        return [
            'cluster' => $cluster->load(['serviceAreas', 'customers']),
        ];
    }

    public function create(array $data): Cluster
    {
        return DB::transaction(function () use ($data) {
            $cluster = Cluster::create([
                'name' => $data['name'],
                'code' => $data['code'],
                // Cluster always starts in Planned state; activation is a separate transition.
                'status' => ClusterStatus::Planned->value,
                'description' => $data['description'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            event(new ClusterCreated($cluster->id, auth()->id()));

            return $cluster->fresh();
        });
    }

    public function update($cluster, array $data): Cluster
    {
        return DB::transaction(function () use ($cluster, $data) {
            $cluster = Cluster::lockForUpdate()->findOrFail($cluster->id);

            $cluster->update([
                'name' => $data['name'],
                'code' => $data['code'],
                'description' => $data['description'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            return $cluster->fresh();
        });
    }

    public function activate(Cluster $cluster): Cluster
    {
        return DB::transaction(function () use ($cluster) {
            $cluster = Cluster::lockForUpdate()->findOrFail($cluster->id);

            if ($cluster->isActive()) {
                return $cluster;
            }

            $cluster->update(['status' => ClusterStatus::Active->value]);

            event(new ClusterActivated($cluster->id, auth()->id()));

            return $cluster->fresh();
        });
    }

    public function inactivate(Cluster $cluster): Cluster
    {
        return DB::transaction(function () use ($cluster) {
            $cluster = Cluster::lockForUpdate()->findOrFail($cluster->id);

            if ($cluster->customers()->where('status', CustomerStatus::Active->value)->exists()) {
                throw ValidationException::withMessages([
                    'cluster' => 'Clusters with active customers cannot be inactivated.',
                ]);
            }

            if ($cluster->serviceAreas()->where('status', ServiceAreaStatus::Active->value)->exists()) {
                throw ValidationException::withMessages([
                    'cluster' => 'Clusters with active service areas cannot be inactivated.',
                ]);
            }

            $cluster->update(['status' => ClusterStatus::Inactive->value]);

            event(new ClusterInactivated($cluster->id, auth()->id()));

            return $cluster->fresh();
        });
    }

    protected function applyDefaultRelationships(Builder $query): Builder
    {
        return $query->withCount(['serviceAreas', 'customers']);
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

        return $query;
    }

    protected function applyDefaultOrdering(Builder $query): Builder
    {
        return $query->orderBy('name');
    }
}