<?php

namespace Database\Factories;

use App\Enums\TireStatus;
use App\Models\Tire;
use App\Models\Truck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tire>
 */
class TireFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => strtoupper(fake()->unique()->bothify('TIRE-###')),
            'status' => TireStatus::Mounted,
            'current_truck_id' => Truck::factory(),
            'current_position' => fake()->randomElement(['FL', 'FR', 'RL1', 'RR1', 'RL2', 'RR2']),
            'purchase_cost' => fake()->randomFloat(2, 60000, 180000),
            'provider_id' => null,
        ];
    }
}
