<?php

namespace App\Policies;

use App\Models\Cluster;
use App\Models\User;

class ClusterPolicy
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

    public function view(User $user, Cluster $cluster): bool
    {
        return $user->hasPermission('service-area.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('service-area.create');
    }

    public function update(User $user, Cluster $cluster): bool
    {
        return $user->hasPermission('service-area.update');
    }

    public function activate(User $user, Cluster $cluster): bool
    {
        return $user->hasPermission('service-area.update');
    }

    public function inactivate(User $user, Cluster $cluster): bool
    {
        return $user->hasPermission('service-area.archive');
    }

    public function delete(User $user, Cluster $cluster): bool
    {
        return false;
    }
}