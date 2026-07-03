<?php

namespace App\Policies;

use App\Models\Fat;
use App\Models\User;

class FatPolicy
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
        return $user->hasPermission('fat.view');
    }

    public function view(User $user, Fat $fat): bool
    {
        return $user->hasPermission('fat.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('fat.create');
    }

    public function update(User $user, Fat $fat): bool
    {
        return $user->hasPermission('fat.update');
    }

    public function activate(User $user, Fat $fat): bool
    {
        return $user->hasPermission('fat.activate');
    }

    public function maintenance(User $user, Fat $fat): bool
    {
        return $user->hasPermission('fat.maintenance');
    }

    public function retire(User $user, Fat $fat): bool
    {
        return $user->hasPermission('fat.retire');
    }

    public function delete(User $user, Fat $fat): bool
    {
        return false;
    }
}
