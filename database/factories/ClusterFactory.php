<?php

namespace Database\Factories;

use App\Enums\ClusterStatus;
use App\Models\Cluster;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cluster>
 */
class ClusterFactory extends Factory
{
    protected $model = Cluster::class;

    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'name' => $name,
            'code' => 'CLU-' . strtoupper(fake()->unique()->bothify('??###')),
            'status' => ClusterStatus::Planned->value,
            'description' => fake()->optional()->sentence(),
            'notes' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => ClusterStatus::Active->value]);
    }

    public function inactive(): static
    {
        return $this->state(['status' => ClusterStatus::Inactive->value]);
    }
}