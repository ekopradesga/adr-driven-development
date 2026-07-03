<?php

namespace App\Services\Package;

use App\Domain\Events\PackageActivated;
use App\Domain\Events\PackageCreated;
use App\Domain\Events\PackageDeprecated;
use App\Domain\Events\PackageRetired;
use App\Enums\PackageStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Package;
use App\Services\AbstractCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PackageService extends AbstractCrudService
{
    protected string $modelClass = Package::class;

    public function buildIndexData(array $filters = [], int $perPage = 25): array
    {
        return [
            'packages' => $this->paginate($filters, $perPage),
            'statuses' => PackageStatus::cases(),
        ];
    }

    public function buildCreateData(): array
    {
        return [];
    }

    public function buildEditData(Package $package): array
    {
        return [
            'package' => $package,
        ];
    }

    public function findForShow(Package $package): array
    {
        return [
            'package' => $package->load([
                'subscriptions' => fn ($query) => $query->latest('id')->limit(10),
                'subscriptions.customer',
            ]),
        ];
    }

    public function create(array $data): Package
    {
        return DB::transaction(function () use ($data) {
            $package = Package::create([
                'package_code' => $data['package_code'],
                'name' => $data['name'],
                'downstream_kbps' => $data['downstream_kbps'],
                'upstream_kbps' => $data['upstream_kbps'],
                'contention_ratio' => $data['contention_ratio'] ?? 1,
                'monthly_price' => $data['monthly_price'],
                'setup_fee' => $data['setup_fee'] ?? 0,
                'billing_cycle_type' => $data['billing_cycle_type'] ?? 'monthly',
                'billing_cycle_days' => $data['billing_cycle_days'] ?? null,
                'status' => PackageStatus::Draft->value,
                'description' => $data['description'] ?? null,
                'created_by' => auth()->id(),
            ]);

            event(new PackageCreated($package->id, auth()->id()));

            return $package->fresh();
        });
    }

    public function update($package, array $data): Package
    {
        return DB::transaction(function () use ($package, $data) {
            $package = Package::lockForUpdate()->findOrFail($package->id);

            if ($package->isRetired()) {
                throw ValidationException::withMessages([
                    'status' => 'Retired packages cannot be edited.',
                ]);
            }

            $package->update([
                'package_code' => $data['package_code'],
                'name' => $data['name'],
                'downstream_kbps' => $data['downstream_kbps'],
                'upstream_kbps' => $data['upstream_kbps'],
                'contention_ratio' => $data['contention_ratio'] ?? 1,
                'monthly_price' => $data['monthly_price'],
                'setup_fee' => $data['setup_fee'] ?? 0,
                'billing_cycle_type' => $data['billing_cycle_type'] ?? 'monthly',
                'billing_cycle_days' => $data['billing_cycle_days'] ?? null,
                'description' => $data['description'] ?? null,
                'updated_by' => auth()->id(),
            ]);

            return $package->fresh();
        });
    }

    public function activate(Package $package): Package
    {
        return DB::transaction(function () use ($package) {
            $package = Package::lockForUpdate()->findOrFail($package->id);

            if ($package->isRetired()) {
                throw ValidationException::withMessages([
                    'status' => 'Retired packages cannot be activated.',
                ]);
            }

            if ($package->isActive()) {
                return $package;
            }

            $package->update([
                'status' => PackageStatus::Active->value,
                'updated_by' => auth()->id(),
            ]);

            event(new PackageActivated($package->id, auth()->id()));

            return $package->fresh();
        });
    }

    public function deprecate(Package $package): Package
    {
        return DB::transaction(function () use ($package) {
            $package = Package::lockForUpdate()->findOrFail($package->id);

            if (!$package->isActive()) {
                throw ValidationException::withMessages([
                    'status' => 'Only active packages can be deprecated.',
                ]);
            }

            $package->update([
                'status' => PackageStatus::Deprecated->value,
                'updated_by' => auth()->id(),
            ]);

            event(new PackageDeprecated($package->id, auth()->id()));

            return $package->fresh();
        });
    }

    public function retire(Package $package): Package
    {
        return DB::transaction(function () use ($package) {
            $package = Package::lockForUpdate()->findOrFail($package->id);

            if ($package->isRetired()) {
                return $package;
            }

            $activeSubscriptions = $package->subscriptions()
                ->where('status', SubscriptionStatus::Active->value)
                ->count();

            if ($activeSubscriptions > 0) {
                throw ValidationException::withMessages([
                    'package' => "Package cannot be retired while {$activeSubscriptions} active subscription(s) still reference it.",
                ]);
            }

            $package->update([
                'status' => PackageStatus::Retired->value,
                'updated_by' => auth()->id(),
            ]);

            event(new PackageRetired($package->id, auth()->id()));

            return $package->fresh();
        });
    }

    protected function applyDefaultRelationships(Builder $query): Builder
    {
        return $query->withCount([
            'subscriptions',
            'subscriptions as active_subscriptions_count' => fn ($builder) => $builder
                ->where('status', SubscriptionStatus::Active->value),
        ]);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('package_code', 'like', "%{$search}%");
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
