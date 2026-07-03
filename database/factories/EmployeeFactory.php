<?php

namespace Database\Factories;

use App\Enums\EmployeeStatus;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => fake()->name(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'status' => EmployeeStatus::Active->value,
            'notes' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => EmployeeStatus::Inactive->value]);
    }

    public function archived(): static
    {
        return $this->state(['status' => EmployeeStatus::Archived->value]);
    }
}