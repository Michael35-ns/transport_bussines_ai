<?php

namespace Database\Factories;

use App\Models\FuelStation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FuelStation>
 */
class FuelStationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' '.fake()->randomElement(['Servicentro', 'Estación']),
            'location' => fake()->city(),
        ];
    }
}
