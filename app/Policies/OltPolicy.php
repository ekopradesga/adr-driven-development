<?php

namespace App\Policies;

use App\Models\Olt;
use App\Models\User;

class OltPolicy
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
        return $user->hasPermission('olt.view');
    }

    public function view(User $user, Olt $olt): bool
    {
        return $user->hasPermission('olt.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('olt.create');
    }

    public function update(User $user, Olt $olt): bool
    {
        return $user->hasPermission('olt.update');
    }

    public function activate(User $user, Olt $olt): bool
    {
        return $user->hasPermission('olt.activate');
    }

    public function maintenance(User $user, Olt $olt): bool
    {
        return $user->hasPermission('olt.maintenance');
    }

    public function retire(User $user, Olt $olt): bool
    {
        return $user->hasPermission('olt.retire');
    }

    public function delete(User $user, Olt $olt): bool
    {
        return false;
    }
}
