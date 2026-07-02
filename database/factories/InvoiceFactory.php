<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $issueDate = $this->faker->dateTimeBetween('-3 months', 'now');
        $dueDate = (clone $issueDate)->modify('+15 days');
        $total = $this->faker->randomFloat(2, 100, 500);

        return [
            'invoice_number'     => 'INV-' . now()->format('Ym') . '-' . str_pad((string) $this->faker->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'customer_id'        => null,
            'subscription_id'    => null,
            'status'             => InvoiceStatus::Draft->value,
            'period_start'       => $issueDate->format('Y-m-d'),
            'period_end'         => (clone $issueDate)->modify('+30 days')->format('Y-m-d'),
            'issue_date'         => $issueDate->format('Y-m-d'),
            'due_date'           => $dueDate->format('Y-m-d'),
            'subtotal_amount'    => $total,
            'tax_amount'         => 0,
            'discount_amount'    => 0,
            'total_amount'       => $total,
            'paid_amount'        => 0,
            'balance_amount'     => $total,
            'published_at'       => null,
            'overdue_at'         => null,
            'cancelled_at'       => null,
            'cancellation_reason'=> null,
            'notes'              => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status'       => InvoiceStatus::Published->value,
            'published_at'  => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'status'      => InvoiceStatus::Overdue->value,
            'overdue_at'  => now(),
        ]);
    }

    public function partiallyPaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'         => InvoiceStatus::PartiallyPaid->value,
            'paid_amount'    => $attributes['total_amount'] / 2,
            'balance_amount' => $attributes['total_amount'] / 2,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'         => InvoiceStatus::Paid->value,
            'paid_amount'    => $attributes['total_amount'],
            'balance_amount' => 0,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status'               => InvoiceStatus::Cancelled->value,
            'cancelled_at'         => now(),
            'cancellation_reason'  => 'Test cancellation',
        ]);
    }
}
