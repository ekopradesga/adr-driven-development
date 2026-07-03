<?php

namespace App\Policies;

use App\Models\CollectionTask;
use App\Models\User;

class CollectionTaskPolicy
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
        return $user->hasPermission('collector.view');
    }

    public function view(User $user, CollectionTask $collectionTask): bool
    {
        return $user->hasPermission('collector.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('collector.create');
    }

    public function update(User $user, CollectionTask $collectionTask): bool
    {
        return $user->hasPermission('collector.update');
    }

    public function assign(User $user, CollectionTask $collectionTask): bool
    {
        return $user->hasPermission('collector.assign');
    }

    public function schedule(User $user, CollectionTask $collectionTask): bool
    {
        return $user->hasPermission('collector.schedule');
    }

    public function startRoute(User $user, CollectionTask $collectionTask): bool
    {
        return $user->hasPermission('collector.route');
    }

    public function recordVisit(User $user, CollectionTask $collectionTask): bool
    {
        return $user->hasPermission('collector.visit');
    }

    public function complete(User $user, CollectionTask $collectionTask): bool
    {
        return $user->hasPermission('collector.complete');
    }

    public function followUpRequired(User $user, CollectionTask $collectionTask): bool
    {
        return $user->hasPermission('collector.update');
    }

    public function cancel(User $user, CollectionTask $collectionTask): bool
    {
        return $user->hasPermission('collector.cancel');
    }

    public function delete(User $user, CollectionTask $collectionTask): bool
    {
        return false;
    }
}