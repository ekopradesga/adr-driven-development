<?php

namespace App\Services\Network;

use App\Domain\Events\WireRouterActivated;
use App\Domain\Events\WireRouterCreated;
use App\Domain\Events\WireRouterMaintenanceStarted;
use App\Domain\Events\WireRouterRetired;
use App\Enums\WireRouterStatus;
use App\Enums\WireRouterType;
use App\Models\WireRouter;
use App\Services\AbstractCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WireRouterService extends AbstractCrudService
{
    protected string $modelClass = WireRouter::class;

    public function buildIndexData(array $filters = [], int $perPage = 25): array
    {
        return [
            'routers' => $this->paginate($filters, $perPage),
            'statuses' => WireRouterStatus::cases(),
            'types' => WireRouterType::cases(),
        ];
    }

    public function buildCreateData(): array
    {
        return [
            'statuses' => WireRouterStatus::cases(),
            'types' => WireRouterType::cases(),
            'parentRouters' => WireRouter::query()->active()->orderBy('name')->get(),
        ];
    }

    public function buildEditData(WireRouter $router): array
    {
        return [
            'router' => $router,
            'statuses' => WireRouterStatus::cases(),
            'types' => WireRouterType::cases(),
            'parentRouters' => WireRouter::query()->whereKeyNot($router->id)->active()->orderBy('name')->get(),
        ];
    }

    public function findForShow(WireRouter $router): array
    {
        return [
            'router' => $router->load([
                'parent',
                'children' => fn ($query) => $query->latest('id')->limit(10),
            ]),
        ];
    }

    public function create(array $data): WireRouter
    {
        return DB::transaction(function () use ($data) {
            $this->guardParentTopology($data['router_type'], $data['parent_router_id'] ?? null);

            $router = WireRouter::create([
                'router_code' => $data['router_code'],
                'name' => $data['name'],
                'router_type' => $data['router_type'],
                'vendor' => $data['vendor'] ?? null,
                'model' => $data['model'] ?? null,
                'ip_address' => $data['ip_address'],
                'snmp_community' => $data['snmp_community'] ?? null,
                'api_username' => $data['api_username'] ?? null,
                'api_password' => $data['api_password'] ?? null,
                'location_name' => $data['location_name'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'parent_router_id' => $data['parent_router_id'] ?? null,
                'status' => WireRouterStatus::Planned->value,
                'last_seen_at' => $data['last_seen_at'] ?? null,
                'created_by' => auth()->id(),
            ]);

            event(new WireRouterCreated($router->id, auth()->id() ?? 0));

            return $router->fresh();
        });
    }

    public function update($router, array $data): WireRouter
    {
        return DB::transaction(function () use ($router, $data) {
            $router = WireRouter::lockForUpdate()->findOrFail($router->id);

            if ($router->isRetired()) {
                throw ValidationException::withMessages([
                    'status' => 'Retired Router assets cannot be edited.',
                ]);
            }

            $this->guardParentTopology($data['router_type'], $data['parent_router_id'] ?? null, $router->id);

            $router->update([
                'router_code' => $data['router_code'],
                'name' => $data['name'],
                'router_type' => $data['router_type'],
                'vendor' => $data['vendor'] ?? null,
                'model' => $data['model'] ?? null,
                'ip_address' => $data['ip_address'],
                'snmp_community' => $data['snmp_community'] ?? null,
                'api_username' => $data['api_username'] ?? null,
                'api_password' => $data['api_password'] ?? null,
                'location_name' => $data['location_name'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'parent_router_id' => $data['parent_router_id'] ?? null,
                'last_seen_at' => $data['last_seen_at'] ?? null,
                'updated_by' => auth()->id(),
            ]);

            return $router->fresh();
        });
    }

    public function activate(WireRouter $router): WireRouter
    {
        return DB::transaction(function () use ($router) {
            $router = WireRouter::lockForUpdate()->findOrFail($router->id);

            if ($router->isRetired()) {
                throw ValidationException::withMessages([
                    'status' => 'Retired Router assets cannot be activated.',
                ]);
            }

            if ($router->isDistribution() && (!$router->parent || !$router->parent->isActive())) {
                throw ValidationException::withMessages([
                    'parent_router_id' => 'Distribution routers require an active core parent before activation.',
                ]);
            }

            if ($router->isActive()) {
                return $router;
            }

            $router->update([
                'status' => WireRouterStatus::Active->value,
                'updated_by' => auth()->id(),
            ]);

            event(new WireRouterActivated($router->id, auth()->id() ?? 0));

            return $router->fresh();
        });
    }

    public function markMaintenance(WireRouter $router): WireRouter
    {
        return DB::transaction(function () use ($router) {
            $router = WireRouter::lockForUpdate()->findOrFail($router->id);

            if (!$router->isActive()) {
                throw ValidationException::withMessages([
                    'status' => 'Only active Router assets can enter maintenance.',
                ]);
            }

            $router->update([
                'status' => WireRouterStatus::Maintenance->value,
                'updated_by' => auth()->id(),
            ]);

            event(new WireRouterMaintenanceStarted($router->id, auth()->id() ?? 0));

            return $router->fresh();
        });
    }

    public function retire(WireRouter $router): WireRouter
    {
        return DB::transaction(function () use ($router) {
            $router = WireRouter::lockForUpdate()->findOrFail($router->id);

            if ($router->isRetired()) {
                return $router;
            }

            $activeChildren = $router->children()
                ->whereNull('deleted_at')
                ->whereIn('status', [WireRouterStatus::Planned->value, WireRouterStatus::Active->value, WireRouterStatus::Maintenance->value])
                ->count();

            if ($activeChildren > 0) {
                throw ValidationException::withMessages([
                    'router' => "Router cannot be retired while {$activeChildren} child router record(s) still depend on it.",
                ]);
            }

            $router->update([
                'status' => WireRouterStatus::Retired->value,
                'updated_by' => auth()->id(),
            ]);

            event(new WireRouterRetired($router->id, auth()->id() ?? 0));

            return $router->fresh();
        });
    }

    protected function applyDefaultRelationships(Builder $query): Builder
    {
        return $query->withCount('children');
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('router_code', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['router_type'])) {
            $query->where('router_type', $filters['router_type']);
        }

        return $query;
    }

    protected function applyDefaultOrdering(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    private function guardParentTopology(string $routerType, mixed $parentRouterId, ?int $ignoreRouterId = null): void
    {
        if ($routerType !== WireRouterType::Distribution->value) {
            return;
        }

        if (empty($parentRouterId)) {
            throw ValidationException::withMessages([
                'parent_router_id' => 'Distribution routers require a parent core router.',
            ]);
        }

        $parentRouter = WireRouter::query()
            ->when($ignoreRouterId, fn (Builder $query) => $query->whereKeyNot($ignoreRouterId))
            ->find($parentRouterId);

        if (!$parentRouter || !$parentRouter->isCore()) {
            throw ValidationException::withMessages([
                'parent_router_id' => 'Distribution routers must reference an existing core router.',
            ]);
        }
    }
}