<?php

namespace Database\Factories;

use App\Models\MaintenanceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceProvider>
 */
class MaintenanceProviderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'contact' => fake()->phoneNumber(),
            'specialty' => fake()->randomElement(['general', 'llantas', 'frenos', 'motor']),
        ];
    }
}
