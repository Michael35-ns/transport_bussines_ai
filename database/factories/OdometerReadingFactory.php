<?php

namespace Database\Factories;

use App\Enums\OdometerSource;
use App\Models\OdometerReading;
use App\Models\Truck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OdometerReading>
 */
class OdometerReadingFactory extends Factory
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
            'read_at' => fake()->dateTimeBetween('-2 months', 'now'),
            'odometer' => fake()->randomFloat(2, 0, 300000),
            'source' => OdometerSource::Manual,
            'source_id' => null,
        ];
    }
}
