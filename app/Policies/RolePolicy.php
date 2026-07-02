<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

/**
 * RolePolicy — Identity & Access module.
 *
 * Governs authorization for role management operations.
 * Super Administrators bypass all checks via the before() gate hook.
 */
class RolePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('role.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasPermission('role.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('role.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasPermission('role.update');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermission('role.delete');
    }

    public function assignPermission(User $user, Role $role): bool
    {
        return $user->hasPermission('role.assign-permission');
    }
}
