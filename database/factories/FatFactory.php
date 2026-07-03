<?php

namespace Database\Factories;

use App\Enums\FatStatus;
use App\Models\Fat;
use App\Models\Odf;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fat>
 */
class FatFactory extends Factory
{
    protected $model = Fat::class;

    public function definition(): array
    {
        return [
            'odf_id' => Odf::factory(),
            'service_area_id' => null,
            'fat_code' => 'FAT-' . strtoupper(fake()->unique()->bothify('??###')),
            'name' => fake()->unique()->streetName(),
            'capacity_ports' => fake()->numberBetween(8, 32),
            'used_ports' => 0,
            'splitter_ratio' => fake()->randomElement(['1:8', '1:16', '1:32']),
            'location_name' => fake()->city(),
            'latitude' => fake()->latitude(-8.0, -6.0),
            'longitude' => fake()->longitude(106.0, 108.0),
            'status' => FatStatus::Planned->value,
            'last_onu_ping_at' => null,
            'notes' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => FatStatus::Active->value]);
    }

    public function maintenance(): static
    {
        return $this->state(['status' => FatStatus::Maintenance->value]);
    }

    public function retired(): static
    {
        return $this->state(['status' => FatStatus::Retired->value]);
    }
}
