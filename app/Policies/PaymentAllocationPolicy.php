<?php

namespace App\Policies;

use App\Models\PaymentAllocation;
use App\Models\User;

class PaymentAllocationPolicy
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
        return $user->hasPermission('payment.view');
    }

    public function view(User $user, PaymentAllocation $allocation): bool
    {
        return $user->hasPermission('payment.view');
    }

    public function reverse(User $user, PaymentAllocation $allocation): bool
    {
        return $user->hasPermission('payment.reverse');
    }

    public function reallocate(User $user, PaymentAllocation $allocation): bool
    {
        return $user->hasPermission('payment.allocate');
    }
}
