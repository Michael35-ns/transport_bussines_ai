<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\RateAgreement;
use App\Models\Route;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RateAgreement>
 */
class RateAgreementFactory extends Factory
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
            'route_id' => Route::factory(),
            'price' => fake()->randomFloat(2, 40000, 350000),
            'valid_from' => fake()->dateTimeBetween('-1 year', 'now'),
            'valid_to' => null,
            'active' => true,
        ];
    }
}
