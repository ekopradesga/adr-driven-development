<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

/**
 * SubscriptionPolicy — Customer Management module.
 * Permission keys follow the subscription.* convention (PermissionSeeder).
 */
class SubscriptionPolicy
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
        return $user->hasPermission('subscription.view');
    }

    public function view(User $user, Subscription $subscription): bool
    {
        return $user->hasPermission('subscription.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('subscription.create');
    }

    public function update(User $user, Subscription $subscription): bool
    {
        return $user->hasPermission('subscription.update');
    }

    public function activate(User $user, Subscription $subscription): bool
    {
        return $user->hasPermission('subscription.update');
    }

    public function suspend(User $user, Subscription $subscription): bool
    {
        return $user->hasPermission('subscription.suspend');
    }

    public function reactivate(User $user, Subscription $subscription): bool
    {
        return $user->hasPermission('subscription.reactivate');
    }

    public function terminate(User $user, Subscription $subscription): bool
    {
        return $user->hasPermission('subscription.terminate');
    }

    public function delete(User $user, Subscription $subscription): bool
    {
        return $user->hasPermission('subscription.update');
    }
}
