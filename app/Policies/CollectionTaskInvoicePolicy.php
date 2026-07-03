<?php

namespace App\Policies;

use App\Models\CollectionTaskInvoice;
use App\Models\User;

class CollectionTaskInvoicePolicy
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

    public function view(User $user, CollectionTaskInvoice $collectionTaskInvoice): bool
    {
        return $user->hasPermission('collector.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('collector.create');
    }

    public function update(User $user, CollectionTaskInvoice $collectionTaskInvoice): bool
    {
        return $user->hasPermission('collector.update');
    }

    public function delete(User $user, CollectionTaskInvoice $collectionTaskInvoice): bool
    {
        return false;
    }
}