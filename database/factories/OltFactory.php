<?php

namespace Database\Factories;

use App\Enums\OltStatus;
use App\Models\Olt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Olt>
 */
class OltFactory extends Factory
{
    protected $model = Olt::class;

    public function definition(): array
    {
        return [
            'olt_code' => 'OLT-' . strtoupper(fake()->unique()->bothify('??###')),
            'name' => fake()->unique()->company(),
            'vendor' => fake()->randomElement(['Huawei', 'ZTE', 'FiberHome']),
            'model' => fake()->bothify('Model-##'),
            'ip_address' => fake()->unique()->ipv4(),
            'snmp_community' => 'public',
            'api_username' => null,
            'api_password' => null,
            'location_name' => fake()->city(),
            'latitude' => fake()->latitude(-8.0, -6.0),
            'longitude' => fake()->longitude(106.0, 108.0),
            'status' => OltStatus::Planned->value,
            'last_seen_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => OltStatus::Active->value]);
    }

    public function maintenance(): static
    {
        return $this->state(['status' => OltStatus::Maintenance->value]);
    }

    public function retired(): static
    {
        return $this->state(['status' => OltStatus::Retired->value]);
    }
}
