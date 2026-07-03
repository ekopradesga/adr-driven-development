<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WireRouter;

class WireRouterPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('router.view');
    }

    public function view(User $user, WireRouter $router): bool
    {
        return $user->hasPermission('router.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('router.create');
    }

    public function update(User $user, WireRouter $router): bool
    {
        return $user->hasPermission('router.update');
    }

    public function delete(User $user, WireRouter $router): bool
    {
        return $user->hasPermission('router.delete');
    }

    public function activate(User $user, WireRouter $router): bool
    {
        return $user->hasPermission('router.activate');
    }

    public function maintenance(User $user, WireRouter $router): bool
    {
        return $user->hasPermission('router.maintenance');
    }

    public function retire(User $user, WireRouter $router): bool
    {
        return $user->hasPermission('router.retire');
    }
}