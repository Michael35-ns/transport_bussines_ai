<?php

namespace Database\Factories;

use App\Enums\CostTypeScope;
use App\Models\CostType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CostType>
 */
class CostTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->unique()->word()),
            'scope' => fake()->randomElement(CostTypeScope::cases()),
        ];
    }
}
