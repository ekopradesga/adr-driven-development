<?php

namespace Database\Factories;

use App\Enums\CollectionTaskPaymentSubmissionStatus;
use App\Enums\CollectionTaskStatus;
use App\Models\CollectionTask;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollectionTask>
 */
class CollectionTaskFactory extends Factory
{
    protected $model = CollectionTask::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'employee_id' => null,
            'status' => CollectionTaskStatus::WaitingAssignment->value,
            'scheduled_for' => null,
            'route_started_at' => null,
            'visited_at' => null,
            'completed_at' => null,
            'follow_up_reason' => null,
            'cancellation_reason' => null,
            'payment_collected_amount' => null,
            'payment_submission_reference' => null,
            'payment_submission_status' => CollectionTaskPaymentSubmissionStatus::Pending->value,
            'notes' => null,
        ];
    }

    public function assigned(): static
    {
        return $this->state(fn () => [
            'employee_id' => Employee::factory(),
            'status' => CollectionTaskStatus::Assigned->value,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn () => [
            'employee_id' => Employee::factory(),
            'status' => CollectionTaskStatus::Scheduled->value,
            'scheduled_for' => now()->addDay(),
        ]);
    }

    public function onRoute(): static
    {
        return $this->state(fn () => [
            'employee_id' => Employee::factory(),
            'status' => CollectionTaskStatus::OnRoute->value,
            'scheduled_for' => now()->addDay(),
            'route_started_at' => now(),
        ]);
    }

    public function customerVisited(): static
    {
        return $this->state(fn () => [
            'employee_id' => Employee::factory(),
            'status' => CollectionTaskStatus::CustomerVisited->value,
            'scheduled_for' => now()->addDay(),
            'route_started_at' => now()->subMinutes(15),
            'visited_at' => now(),
        ]);
    }
}