<?php

namespace App\Services\Settings;

use App\Enums\SettingDataType;
use App\Enums\SettingScope;
use App\Models\Setting;
use App\Models\SettingCategory;
use App\Models\SettingRegistryEntry;
use App\Services\AbstractCrudService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * SettingService — Settings Engine.
 *
 * The authoritative service for reading and writing platform configuration.
 * All business rules that require configurable thresholds, schedules, or policies
 * must retrieve values through this service — never hardcode them.
 *
 * Scope resolution order (highest priority first):
 *   Customer → Area/Cluster → Global → Registry default
 *
 * Values are cached per key+scope+scopeId combination.
 * Registered as a singleton in AppServiceProvider.
 */
class SettingService extends AbstractCrudService
{
    protected string $modelClass = Setting::class;
    private const CACHE_TTL    = 3600;
    private const CACHE_PREFIX = 'setting:';

    /**
     * Retrieve a typed setting value.
     *
     * @param string       $key     The setting key (e.g. 'billing.due_days').
     * @param SettingScope $scope   The scope to look up. Falls back to global if not found.
     * @param int          $scopeId The scoped entity ID (0 for global).
     */
    public function get(
        string $key,
        SettingScope $scope = SettingScope::Global,
        int $scopeId = 0
    ): mixed {
        $cacheKey = $this->cacheKey($key, $scope, $scopeId);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $scope, $scopeId) {
            // Attempt scoped lookup first
            if ($scope !== SettingScope::Global) {
                $setting = $this->findSetting($key, $scope, $scopeId);
                if ($setting !== null) {
                    return $setting->getTypedValue();
                }
            }

            // Fall back to global
            $global = $this->findSetting($key, SettingScope::Global, 0);
            if ($global !== null) {
                return $global->getTypedValue();
            }

            // Fall back to registry entry default
            return $this->registryDefault($key);
        });
    }

    /**
     * Persist a setting value for the given key and scope.
     * Invalidates the cache entry on write.
     */
    public function set(
        string $key,
        mixed $value,
        SettingScope $scope = SettingScope::Global,
        int $scopeId = 0,
        bool $isActive = true
    ): Setting {
        $registryEntry = SettingRegistryEntry::where('key', $key)->firstOrFail();

        $setting = Setting::updateOrCreate(
            [
                'key'      => $key,
                'scope'    => $scope->value,
                'scope_id' => $scopeId,
            ],
            [
                'registry_entry_id' => $registryEntry->id,
                'value'             => $this->normalizeValue($value),
                'is_active'         => $isActive,
            ]
        );

        Cache::forget($this->cacheKey($key, $scope, $scopeId));

        return $setting;
    }

    /**
     * Paginated settings list for index pages.
     */
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;
        $category = $filters['category'] ?? null;
        $scope = $filters['scope'] ?? null;
        $isActive = $filters['is_active'] ?? null;

        return Setting::query()
            ->with(['registryEntry.category'])
            ->when(
                filled($search),
                fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('key', 'like', '%' . $search . '%')
                        ->orWhereHas('registryEntry', function ($query) use ($search) {
                            $query->where('name', 'like', '%' . $search . '%');
                        });
                })
            )
            ->when(
                filled($category),
                fn ($query) => $query->whereHas('registryEntry.category', function ($query) use ($category) {
                    $query->where('code', $category);
                })
            )
            ->when(
                filled($scope),
                fn ($query) => $query->where('scope', $scope)
            )
            ->when(
                $isActive !== null && $isActive !== '',
                fn ($query) => $query->where('is_active', (bool) $isActive)
            )
            ->orderByRaw('(select sort_order from setting_categories where setting_categories.id = setting_registry_entries.category_id limit 1) asc')
            ->join('setting_registry_entries', 'setting_registry_entries.id', '=', 'settings.registry_entry_id')
            ->select('settings.*')
            ->orderBy('setting_registry_entries.key')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Resolve a setting for edit form display.
     */
    public function findForEdit(Setting $setting): Setting
    {
        return $setting->loadMissing(['registryEntry.category']);
    }

    /**
     * Build data for index page.
     */
    public function buildIndexData(array $filters = [], int $perPage = 25): array
    {
        return [
            'settings'   => $this->paginate($filters, $perPage),
            'categories' => $this->categories(),
            'scopes'     => SettingScope::cases(),
        ];
    }

    /**
     * Build data for edit form.
     */
    public function buildEditData(Setting $setting): array
    {
        return [
            'setting'   => $this->findForEdit($setting),
            'canUpdate' => auth()->user()?->can('update', $setting) ?? false,
        ];
    }

    /**
     * Update value/active state for an existing setting record.
     */
    public function updateSetting(Setting $setting, mixed $value, bool $isActive): Setting
    {
        return $this->set(
            key: $setting->key,
            value: $value,
            scope: $setting->scope,
            scopeId: $setting->scope_id,
            isActive: $isActive
        )->loadMissing(['registryEntry.category']);
    }

    /**
     * Available setting categories for filters and grouping.
     */
    public function categories(): Collection
    {
        return SettingCategory::query()
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get(['id', 'code', 'label']);
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    private function findSetting(string $key, SettingScope $scope, int $scopeId): ?Setting
    {
        return Setting::query()
            ->with('registryEntry')
            ->where('key', $key)
            ->where('scope', $scope->value)
            ->where('scope_id', $scopeId)
            ->where('is_active', true)
            ->first();
    }

    private function registryDefault(string $key): mixed
    {
        $entry = SettingRegistryEntry::where('key', $key)->first();

        if ($entry === null || $entry->default_value === null) {
            return null;
        }

        return match ($entry->data_type) {
            SettingDataType::Integer => (int) $entry->default_value,
            SettingDataType::Boolean => filter_var($entry->default_value, FILTER_VALIDATE_BOOLEAN),
            SettingDataType::Json    => json_decode($entry->default_value, true),
            SettingDataType::Decimal => (float) $entry->default_value,
            default                  => $entry->default_value,
        };
    }

    private function cacheKey(string $key, SettingScope $scope, int $scopeId): string
    {
        return self::CACHE_PREFIX . $key . ':' . $scope->value . ':' . $scopeId;
    }

    private function normalizeValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }
}
