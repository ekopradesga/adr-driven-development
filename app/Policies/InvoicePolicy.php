<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

/**
 * InvoicePolicy — Billing module.
 *
 * Governs authorization for invoice lifecycle operations.
 * Super Administrators bypass all checks via the before() gate hook.
 */
class InvoicePolicy
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
        return $user->hasPermission('billing.view');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('billing.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('billing.generate');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('billing.generate');
    }

    public function publish(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('billing.publish');
    }

    public function cancel(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('billing.void');
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('billing.void');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('billing.export');
    }
}
