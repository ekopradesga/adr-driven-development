<?php

namespace App\Services\Identity;

use App\Enums\RoleStatus;
use App\Models\Permission;
use App\Models\Role;
use App\Services\AbstractCrudService;
use Illuminate\Database\Eloquent\Builder;

/**
 * RoleService — Identity & Access module.
 *
 * Owns all role management business logic.
 */
class RoleService extends AbstractCrudService
{
    protected string $modelClass = Role::class;

    /**
     * Alias for paginate() to maintain backward compatibility.
     */
    public function list(array $filters = [], int $perPage = 25)
    {
        return $this->paginate($filters, $perPage);
    }

    public function create(array $data): Role
    {
        return Role::create([
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'description' => $data['description'] ?? null,
            'status'      => $data['status'] ?? RoleStatus::Active->value,
        ]);
    }

    public function update($role, array $data): Role
    {
        $role->update([
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'description' => $data['description'] ?? null,
            'status'      => $data['status'] ?? $role->status->value,
        ]);

        return $role;
    }

    /**
     * Deletes a role.
     * Per erd.md: Restrict when assigned. Caller must validate before calling.
     *
     * @param \App\Models\Role $role
     */
    public function delete($role): void
    {
        $role->permissions()->detach();
        $role->delete();
    }

    public function assignPermission(Role $role, Permission $permission): void
    {
        $role->permissions()->syncWithoutDetaching([$permission->id]);
    }

    public function removePermission(Role $role, Permission $permission): void
    {
        $role->permissions()->detach($permission->id);
    }

    public function syncPermissions(Role $role, array $permissionIds): void
    {
        $role->permissions()->sync($permissionIds);
    }

    /**
     * Load role with users for display.
     */
    public function findForShow(Role $role): array
    {
        $role->loadMissing('users');
        $permissions = $this->activePermissions();

        return [
            'role'           => $role,
            'allPermissions' => $permissions['all'],
            'assignedIds'    => $this->assignedPermissionIds($role),
        ];
    }

    /**
     * Load role with assigned roles for edit form.
     */
    public function findForEdit(Role $role): Role
    {
        return $role->loadMissing('permissions');
    }

    /**
     * Build data for index page.
     */
    public function buildIndexData(array $filters = [], int $perPage = 25): array
    {
        return [
            'roles'    => $this->paginate($filters, $perPage),
            'statuses' => RoleStatus::cases(),
        ];
    }

    /**
     * Build data for create form.
     */
    public function buildCreateData(): array
    {
        return [
            'statuses' => RoleStatus::cases(),
        ];
    }

    /**
     * Build data for edit form.
     */
    public function buildEditData(Role $role): array
    {
        $role = $this->findForEdit($role);
        $permissions = $this->activePermissions();

        return [
            'role'           => $role,
            'statuses'       => RoleStatus::cases(),
            'allPermissions' => $permissions['all'],
            'assignedIds'    => $this->assignedPermissionIds($role),
        ];
    }

    /**
     * Get all active permissions grouped by category.
     * Returns Collection with permission IDs assigned to the role.
     */
    public function activePermissions(): array
    {
        $allPermissions = Permission::active()
            ->orderBy('category')
            ->orderBy('key')
            ->get()
            ->groupBy('category');

        return [
            'all' => $allPermissions,
        ];
    }

    /**
     * Get assigned permission IDs for a role.
     */
    public function assignedPermissionIds(Role $role): array
    {
        return $role->permissions()->pluck('permissions.id')->toArray();
    }

    /**
     * Check if role can be deleted (not assigned to users).
     */
    public function canDelete(Role $role): bool
    {
        return $role->users()->count() === 0;
    }

    // -------------------------------------------------------------------------
    // AbstractCrudService Customization
    // -------------------------------------------------------------------------

    protected function applyDefaultRelationships(Builder $query): Builder
    {
        return $query->withCount(['users', 'permissions']);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
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
