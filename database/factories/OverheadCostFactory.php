<?php

namespace Database\Factories;

use App\Enums\BillingCycle;
use App\Models\CostType;
use App\Models\OverheadCost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OverheadCost>
 */
class OverheadCostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cost_type_id' => CostType::factory(),
            'amount' => fake()->randomFloat(2, 100000, 2000000),
            'billing_cycle' => BillingCycle::Monthly,
            'effective_from' => fake()->dateTimeBetween('-1 year', 'now'),
            'effective_to' => null,
        ];
    }
}
