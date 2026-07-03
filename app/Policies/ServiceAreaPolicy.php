<?php

namespace App\Policies;

use App\Models\ServiceArea;
use App\Models\User;

class ServiceAreaPolicy
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
        return $user->hasPermission('service-area.view');
    }

    public function view(User $user, ServiceArea $serviceArea): bool
    {
        return $user->hasPermission('service-area.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('service-area.create');
    }

    public function update(User $user, ServiceArea $serviceArea): bool
    {
        return $user->hasPermission('service-area.update');
    }

    public function activate(User $user, ServiceArea $serviceArea): bool
    {
        return $user->hasPermission('service-area.update');
    }

    public function merge(User $user, ServiceArea $serviceArea): bool
    {
        return $user->hasPermission('service-area.merge');
    }

    public function archive(User $user, ServiceArea $serviceArea): bool
    {
        return $user->hasPermission('service-area.archive');
    }

    public function assignEmployee(User $user, ServiceArea $serviceArea): bool
    {
        return $user->hasPermission('service-area.assign');
    }

    public function removeEmployee(User $user, ServiceArea $serviceArea): bool
    {
        return $user->hasPermission('service-area.assign');
    }

    public function delete(User $user, ServiceArea $serviceArea): bool
    {
        return false;
    }
}