<?php

namespace Database\Factories;

use App\Models\Fat;
use App\Models\Olt;
use App\Models\Onu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Onu>
 */
class OnuFactory extends Factory
{
    protected $model = Onu::class;

    public function definition(): array
    {
        return [
            'olt_id' => Olt::factory(),
            'fat_id' => null,
            'onu_sn' => strtoupper(fake()->unique()->bothify('ONU########')),
            'onu_index' => fake()->numberBetween(1, 128),
            'pon_port' => 'PON-' . fake()->numberBetween(1, 16),
            'model' => fake()->bothify('ONU-##'),
            'customer_label' => null,
            'status' => 'unknown',
            'rx_power_dbm' => null,
            'tx_power_dbm' => null,
            'last_seen_at' => null,
            'provisioned_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active', 'last_seen_at' => now()]);
    }

    public function offline(): static
    {
        return $this->state(['status' => 'offline']);
    }

    public function forFat(Fat $fat): static
    {
        return $this->state(['fat_id' => $fat->id, 'olt_id' => $fat->odf->olt_id]);
    }
}
