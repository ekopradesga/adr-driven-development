<?php

namespace Database\Factories;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    private static int $counter = 0;

    public function definition(): array
    {
        self::$counter++;
        $number = str_pad(self::$counter, 6, '0', STR_PAD_LEFT);

        return [
            'customer_number' => "CUST-{$number}",
            'name'            => fake()->name(),
            'customer_type'   => CustomerType::Individual->value,
            'email'           => fake()->unique()->safeEmail(),
            'phone'           => fake()->phoneNumber(),
            'whatsapp_phone'  => null,
            'alt_phone'       => null,
            'address'         => fake()->address(),
            'latitude'        => fake()->latitude(-8.0, -6.0),
            'longitude'       => fake()->longitude(106.0, 108.0),
            'notes'           => null,
            'cluster_id'      => null,
            'service_area_id' => null,
            'user_id'         => null,
            'status'          => CustomerStatus::Prospect->value,
        ];
    }

    /** State: Customer in Active status. */
    public function active(): static
    {
        return $this->state(['status' => CustomerStatus::Active->value]);
    }

    /** State: Customer in Suspended status. */
    public function suspended(): static
    {
        return $this->state(['status' => CustomerStatus::Suspended->value]);
    }

    /** State: Customer in Terminated status. */
    public function terminated(): static
    {
        return $this->state(['status' => CustomerStatus::Terminated->value]);
    }

    /** State: Business customer type. */
    public function business(): static
    {
        return $this->state(['customer_type' => CustomerType::Business->value]);
    }
}
