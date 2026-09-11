<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\DriverWorklog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DriverWorklog>
 */
class DriverWorklogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hours = fake()->randomFloat(2, 4, 10);
        $rate = fake()->randomFloat(4, 900, 2000);

        return [
            'driver_id' => Driver::factory(),
            'work_date' => fake()->dateTimeBetween('-2 months', 'now'),
            'hours' => $hours,
            'hourly_rate_snapshot' => $rate,
            'computed_pay' => round($hours * $rate, 2),
            'notes' => null,
            'created_by' => User::factory(),
        ];
    }
}
