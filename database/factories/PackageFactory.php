<?php

namespace Database\Factories;

use App\Enums\PackageStatus;
use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition(): array
    {
        return [
            'package_code' => 'PKG-' . strtoupper(fake()->unique()->bothify('??###')),
            'name' => fake()->unique()->words(2, true),
            'downstream_kbps' => fake()->numberBetween(10000, 200000),
            'upstream_kbps' => fake()->numberBetween(5000, 100000),
            'contention_ratio' => fake()->numberBetween(1, 8),
            'monthly_price' => fake()->numberBetween(150000, 900000),
            'setup_fee' => fake()->numberBetween(0, 300000),
            'billing_cycle_type' => 'monthly',
            'billing_cycle_days' => null,
            'status' => PackageStatus::Draft->value,
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => PackageStatus::Active->value]);
    }

    public function deprecated(): static
    {
        return $this->state(['status' => PackageStatus::Deprecated->value]);
    }

    public function retired(): static
    {
        return $this->state(['status' => PackageStatus::Retired->value]);
    }
}
