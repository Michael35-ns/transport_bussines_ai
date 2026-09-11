<?php

namespace Database\Factories;

use App\Enums\BillingCycle;
use App\Models\CostType;
use App\Models\Truck;
use App\Models\TruckFixedCost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TruckFixedCost>
 */
class TruckFixedCostFactory extends Factory
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
            'cost_type_id' => CostType::factory(),
            'amount' => fake()->randomFloat(2, 10000, 200000),
            'billing_cycle' => fake()->randomElement(BillingCycle::cases()),
            'effective_from' => fake()->dateTimeBetween('-1 year', 'now'),
            'effective_to' => null,
        ];
    }
}
