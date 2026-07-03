<?php

namespace Database\Factories;

use App\Enums\WireRouterStatus;
use App\Enums\WireRouterType;
use App\Models\WireRouter;
use Illuminate\Database\Eloquent\Factories\Factory;

class WireRouterFactory extends Factory
{
    protected $model = WireRouter::class;

    public function definition(): array
    {
        return [
            'router_code' => 'RTR-' . $this->faker->unique()->numerify('######'),
            'name' => $this->faker->company() . ' Router',
            'router_type' => WireRouterType::Core->value,
            'vendor' => $this->faker->randomElement(['Cisco', 'MikroTik', 'Huawei']),
            'model' => $this->faker->bothify('??-####'),
            'ip_address' => $this->faker->unique()->ipv4(),
            'snmp_community' => 'public',
            'api_username' => $this->faker->userName(),
            'api_password' => 'secret',
            'location_name' => $this->faker->city(),
            'latitude' => $this->faker->latitude(10, 15),
            'longitude' => $this->faker->longitude(100, 105),
            'status' => WireRouterStatus::Planned->value,
            'last_seen_at' => now(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => WireRouterStatus::Active->value]);
    }

    public function maintenance(): static
    {
        return $this->state(fn () => ['status' => WireRouterStatus::Maintenance->value]);
    }

    public function retired(): static
    {
        return $this->state(fn () => ['status' => WireRouterStatus::Retired->value]);
    }

    public function distribution(): static
    {
        return $this->state(fn () => ['router_type' => WireRouterType::Distribution->value]);
    }
}