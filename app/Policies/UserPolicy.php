<?php

namespace App\Policies;

use App\Models\User;

/**
 * UserPolicy — Identity & Access module.
 *
 * Governs authorization for user management operations.
 * Super Administrators bypass all checks via the before() gate hook.
 *
 * All permission keys follow the user.* convention (PermissionSeeder).
 */
class UserPolicy
{
    /**
     * Super administrators bypass all authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('user.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermission('user.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('user.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasPermission('user.update');
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasPermission('user.delete');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->hasPermission('user.restore');
    }

    public function assignRole(User $user, User $model): bool
    {
        return $user->hasPermission('user.assign-role');
    }
}
