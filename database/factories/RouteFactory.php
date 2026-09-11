<?php

namespace Database\Factories;

use App\Models\Route;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Route>
 */
class RouteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $origin = fake()->city();
        $destination = fake()->city();

        return [
            'name' => "{$origin} - {$destination}",
            'origin' => $origin,
            'destination' => $destination,
            'standard_km' => fake()->randomFloat(2, 20, 400),
            'typical_toll_cost' => fake()->randomFloat(2, 0, 3000),
            'is_round_trip' => fake()->boolean(30),
        ];
    }
}
