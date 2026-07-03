<?php

namespace Database\Factories;

use App\Enums\ServiceAreaLevel;
use App\Enums\ServiceAreaStatus;
use App\Models\Cluster;
use App\Models\ServiceArea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceArea>
 */
class ServiceAreaFactory extends Factory
{
    protected $model = ServiceArea::class;

    public function definition(): array
    {
        return [
            'cluster_id' => Cluster::factory(),
            'parent_id' => null,
            'merged_into_service_area_id' => null,
            'name' => fake()->unique()->streetName(),
            'code' => 'SAR-' . strtoupper(fake()->unique()->bothify('??###')),
            'level' => ServiceAreaLevel::Area->value,
            'boundary_geojson' => null,
            'center_latitude' => fake()->latitude(-8.0, -6.0),
            'center_longitude' => fake()->longitude(106.0, 108.0),
            'status' => ServiceAreaStatus::Draft->value,
            'merged_at' => null,
            'archived_at' => null,
            'notes' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => ServiceAreaStatus::Active->value]);
    }

    public function merged(ServiceArea $destination): static
    {
        return $this->state([
            'status' => ServiceAreaStatus::Merged->value,
            'merged_into_service_area_id' => $destination->id,
            'merged_at' => now(),
        ]);
    }

    public function archived(): static
    {
        return $this->state([
            'status' => ServiceAreaStatus::Archived->value,
            'archived_at' => now(),
        ]);
    }
}