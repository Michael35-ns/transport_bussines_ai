<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'paid_at' => fake()->dateTimeBetween('-2 months', 'now'),
            'amount' => fake()->randomFloat(2, 40000, 500000),
            'method' => fake()->randomElement(['cash', 'transfer']),
            'reference' => fake()->bothify('REF-#####'),
            'created_by' => User::factory(),
        ];
    }
}
