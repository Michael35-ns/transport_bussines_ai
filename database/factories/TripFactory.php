<?php

namespace Database\Factories;

use App\Enums\TripStatus;
use App\Models\Driver;
use App\Models\Route;
use App\Models\Trip;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trip>
 */
class TripFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-2 months', 'now');

        return [
            'truck_id' => Truck::factory(),
            'driver_id' => Driver::factory(),
            'route_id' => Route::factory(),
            'rate_agreement_id' => null,
            'planned_start' => $start,
            'actual_start' => $start,
            'actual_end' => (clone $start)->modify('+'.fake()->numberBetween(1, 10).' hours'),
            // Distance defaults to the route's standard_km (docs/decisions/0002);
            // callers should sync this after resolving the route relation.
            'distance' => fake()->randomFloat(2, 20, 400),
            'distance_estimated' => true,
            'price' => fake()->randomFloat(2, 40000, 350000),
            'status' => TripStatus::Completed,
            'created_by' => User::factory(),
        ];
    }
}
