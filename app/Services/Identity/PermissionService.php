<?php

namespace App\Services\Identity;

use App\Models\Permission;
use App\Services\AbstractCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * PermissionService — Identity & Access module.
 *
 * Owns all permission management business logic.
 * Permissions are seeder-governed; no create/update/delete operations.
 */
class PermissionService extends AbstractCrudService
{
    protected string $modelClass = Permission::class;

    /**
     * List of distinct permission categories.
     */
    public function categories(): Collection
    {
        return Permission::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
    }

    /**
     * Load permission with all assigned roles for display.
     */
    public function findForShow(Permission $permission): array
    {
        return [
            'permission' => $permission->loadMissing('roles'),
        ];
    }

    /**
     * Build data for index page.
     */
    public function buildIndexData(array $filters = [], int $perPage = 50): array
    {
        return [
            'permissions' => $this->paginate($filters, $perPage),
            'categories'  => $this->categories(),
        ];
    }

    // -------------------------------------------------------------------------
    // AbstractCrudService Customization
    // -------------------------------------------------------------------------

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        return $query;
    }

    protected function applyDefaultOrdering(Builder $query): Builder
    {
        return $query->orderBy('category')->orderBy('key');
    }
}
