<?php

namespace Database\Factories;

use App\Enums\ActiveStatus;
use App\Models\Driver;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Driver>
 */
class DriverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'document_id' => fake()->numerify('#-####-####'),
            'license_class' => fake()->randomElement(['B1', 'B2', 'B3', 'C1']),
            'license_expiry' => fake()->dateTimeBetween('+6 months', '+5 years'),
            'hire_date' => fake()->dateTimeBetween('-6 years', '-1 month'),
            'hourly_rate' => fake()->randomFloat(4, 900, 2000),
            'status' => ActiveStatus::Active,
        ];
    }
}
