<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

/**
 * CustomerPolicy — Customer Management module.
 *
 * Governs authorization for Customer management operations.
 * Super Administrators bypass all checks via the before() gate hook.
 *
 * Permission keys follow the customer.* convention (PermissionSeeder).
 * Available permissions: customer.view, customer.create, customer.update,
 *                        customer.delete, customer.export
 */
class CustomerPolicy
{
    /**
     * Super administrators bypass all authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('customer.view');
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->hasPermission('customer.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('customer.create');
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->hasPermission('customer.update');
    }

    /**
     * Suspend a Customer account (administrative action).
     * Requires customer.update permission — suspension is an update-class operation.
     * The Customer must be in Active state (enforced by CustomerService).
     */
    public function suspend(User $user, Customer $customer): bool
    {
        return $user->hasPermission('customer.update');
    }

    /**
     * Reactivate a suspended Customer account.
     * Requires customer.update permission.
     */
    public function reactivate(User $user, Customer $customer): bool
    {
        return $user->hasPermission('customer.update');
    }

    /**
     * Terminate a Customer account (permanent closure).
     * Requires customer.delete permission — termination is a destructive operation.
     */
    public function terminate(User $user, Customer $customer): bool
    {
        return $user->hasPermission('customer.delete');
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->hasPermission('customer.delete');
    }

    public function restore(User $user, Customer $customer): bool
    {
        return $user->hasPermission('customer.restore');
    }
}
