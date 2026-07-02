<?php

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionType;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'customer_id'       => null,
            'package_id'        => null,
            'onu_id'            => null,
            'status'            => SubscriptionStatus::Pending->value,
            'subscription_type' => SubscriptionType::Primary->value,
            'suspension_type'   => null,
            'suspension_reason' => null,
            'suspended_at'      => null,
            'activated_at'      => null,
            'reactivation_requested_at' => null,
            'terminated_at'     => null,
            'terminated_reason' => null,
            'billing_day'       => 1,
            'notes'             => null,
        ];
    }

    public function active(): static
    {
        return $this->state([
            'status'       => SubscriptionStatus::Active->value,
            'activated_at' => now(),
        ]);
    }

    public function suspended(string $type = 'manual'): static
    {
        return $this->state([
            'status'           => SubscriptionStatus::Suspended->value,
            'suspension_type'  => $type,
            'suspension_reason'=> 'Test suspension',
            'suspended_at'     => now(),
        ]);
    }

    public function terminated(): static
    {
        return $this->state([
            'status'           => SubscriptionStatus::Terminated->value,
            'terminated_at'    => now(),
            'terminated_reason'=> 'Test termination',
        ]);
    }

    public function reactivationPending(): static
    {
        return $this->state([
            'status'                    => SubscriptionStatus::ReactivationPending->value,
            'reactivation_requested_at' => now(),
        ]);
    }
}
