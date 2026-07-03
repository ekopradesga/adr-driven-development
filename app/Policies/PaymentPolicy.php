<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
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

    public function view(User $user, Payment $payment): bool
    {
        return $user->hasPermission('payment.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('payment.create');
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->hasPermission('payment.create');
    }

    public function receive(User $user, Payment $payment): bool
    {
        return $user->hasPermission('payment.create');
    }

    public function validatePayment(User $user, Payment $payment): bool
    {
        return $user->hasPermission('payment.create');
    }

    public function record(User $user, Payment $payment): bool
    {
        return $user->hasPermission('payment.create');
    }

    public function allocate(User $user, Payment $payment): bool
    {
        return $user->hasPermission('payment.allocate');
    }

    public function complete(User $user, Payment $payment): bool
    {
        return $user->hasPermission('payment.allocate');
    }

    public function reverse(User $user, Payment $payment): bool
    {
        return $user->hasPermission('payment.reverse');
    }

    public function fail(User $user, Payment $payment): bool
    {
        return $user->hasPermission('payment.reverse');
    }

    public function delete(User $user, Payment $payment): bool
    {
        return false;
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('payment.export');
    }
}
