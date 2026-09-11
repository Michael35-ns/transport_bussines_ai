<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\TollRecord;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TollRecord>
 */
class TollRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'truck_id' => Truck::factory(),
            'trip_id' => null,
            'occurred_at' => fake()->dateTimeBetween('-2 months', 'now'),
            'location' => fake()->streetName(),
            'amount' => fake()->randomFloat(2, 500, 3000),
            'payment_method' => PaymentMethod::Cash,
            'created_by' => User::factory(),
        ];
    }
}
