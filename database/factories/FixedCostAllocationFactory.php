<?php

namespace Database\Factories;

use App\Enums\AllocationMethod;
use App\Models\FixedCostAllocation;
use App\Models\Truck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FixedCostAllocation>
 */
class FixedCostAllocationFactory extends Factory
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
            'period_start' => $start,
            'period_end' => (clone $start)->modify('+6 days'),
            'method' => AllocationMethod::WorkedDays,
            'weight' => fake()->randomFloat(6, 0, 1),
            'amount' => fake()->randomFloat(2, 10000, 300000),
        ];
    }
}
