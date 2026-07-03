<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $paymentDate = fake()->dateTimeBetween('-30 days', 'now');

        return [
            'payment_number' => 'PAY-' . now()->format('Ym') . '-' . str_pad((string) fake()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'customer_id' => Customer::factory(),
            'status' => PaymentStatus::IntentCreated->value,
            'payment_date' => $paymentDate,
            'amount' => fake()->randomFloat(2, 10000, 500000),
            'currency' => 'IDR',
            'method' => fake()->randomElement(['cash', 'bank_transfer', 'va', 'qris', 'card', 'other']),
            'channel_reference' => fake()->optional()->bothify('REF-########'),
            'received_by' => null,
            'recorded_at' => null,
            'completed_at' => null,
            'reversed_at' => null,
            'reversal_reason' => null,
            'failure_reason' => null,
            'notes' => null,
        ];
    }

    public function recorded(): static
    {
        return $this->state([
            'status' => PaymentStatus::Recorded->value,
            'recorded_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state([
            'status' => PaymentStatus::Completed->value,
            'recorded_at' => now()->subMinute(),
            'completed_at' => now(),
        ]);
    }
}
