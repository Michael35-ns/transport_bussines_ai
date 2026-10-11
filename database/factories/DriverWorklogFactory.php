<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\DriverWorklog;
use App\Models\User;
use App\Services\Financial\Period;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

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
        // A week's total, not a single day's — work_date is normalized to
        // that week's start the same way DriverWorklogs\Index::save() does.
        $hours = fake()->randomFloat(2, 20, 50);
        $rate = fake()->randomFloat(4, 900, 2000);

        return [
            'driver_id' => Driver::factory(),
            'work_date' => Period::weekContaining(Carbon::parse(fake()->dateTimeBetween('-2 months', 'now')))->start,
            'hours' => $hours,
            'hourly_rate_snapshot' => $rate,
            'computed_pay' => round($hours * $rate, 2),
            'notes' => null,
            'created_by' => User::factory(),
        ];
    }
}
