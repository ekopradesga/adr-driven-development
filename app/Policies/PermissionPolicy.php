<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

/**
 * PermissionPolicy — Identity & Access module.
 *
 * Permissions are seeded — they cannot be created or deleted through the UI.
 * This policy only allows viewing, which requires the role.view permission.
 */
class PermissionPolicy
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

    public function view(User $user, Permission $permission): bool
    {
        return $user->hasPermission('role.view');
    }
}
