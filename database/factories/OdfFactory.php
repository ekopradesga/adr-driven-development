<?php

namespace Database\Factories;

use App\Enums\OdfStatus;
use App\Models\Odf;
use App\Models\Olt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Odf>
 */
class OdfFactory extends Factory
{
    protected $model = Odf::class;

    public function definition(): array
    {
        return [
            'olt_id' => Olt::factory(),
            'odf_code' => 'ODF-' . strtoupper(fake()->unique()->bothify('??###')),
            'name' => fake()->unique()->streetName(),
            'location_name' => fake()->city(),
            'latitude' => fake()->latitude(-8.0, -6.0),
            'longitude' => fake()->longitude(106.0, 108.0),
            'status' => OdfStatus::Active->value,
            'notes' => null,
        ];
    }
}
