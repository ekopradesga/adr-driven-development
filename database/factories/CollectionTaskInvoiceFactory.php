<?php

namespace Database\Factories;

use App\Enums\CollectionTaskInvoiceStatus;
use App\Models\CollectionTask;
use App\Models\CollectionTaskInvoice;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollectionTaskInvoice>
 */
class CollectionTaskInvoiceFactory extends Factory
{
    protected $model = CollectionTaskInvoice::class;

    public function definition(): array
    {
        return [
            'collection_task_id' => CollectionTask::factory(),
            'invoice_id' => Invoice::factory(),
            'status' => CollectionTaskInvoiceStatus::Created->value,
            'inclusion_reason' => fake()->sentence(),
            'resolution_outcome' => null,
            'resolved_at' => null,
            'cancelled_at' => null,
            'notes' => null,
        ];
    }

    public function resolved(): static
    {
        return $this->state([
            'status' => CollectionTaskInvoiceStatus::Resolved->value,
            'resolution_outcome' => 'settled',
            'resolved_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => CollectionTaskInvoiceStatus::Cancelled->value,
            'cancelled_at' => now(),
        ]);
    }
}