<?php

namespace App\Policies;

use App\Models\Package;
use App\Models\User;

class PackagePolicy
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
        return $user->hasPermission('package.view');
    }

    public function view(User $user, Package $package): bool
    {
        return $user->hasPermission('package.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('package.create');
    }

    public function update(User $user, Package $package): bool
    {
        return $user->hasPermission('package.update');
    }

    public function activate(User $user, Package $package): bool
    {
        return $user->hasPermission('package.activate');
    }

    public function deprecate(User $user, Package $package): bool
    {
        return $user->hasPermission('package.deprecate');
    }

    public function retire(User $user, Package $package): bool
    {
        return $user->hasPermission('package.retire');
    }

    public function delete(User $user, Package $package): bool
    {
        return false;
    }
}
