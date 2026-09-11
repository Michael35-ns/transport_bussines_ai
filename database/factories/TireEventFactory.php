<?php

namespace Database\Factories;

use App\Enums\TireEventType;
use App\Models\Tire;
use App\Models\TireEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TireEvent>
 */
class TireEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tire_id' => Tire::factory(),
            'truck_id' => null,
            'event_type' => fake()->randomElement(TireEventType::cases()),
            'position' => fake()->randomElement(['FL', 'FR', 'RL1', 'RR1', 'RL2', 'RR2']),
            'occurred_at' => fake()->dateTimeBetween('-2 months', 'now'),
            'notes' => null,
        ];
    }
}
