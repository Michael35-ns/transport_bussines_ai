<?php

namespace Database\Factories;

use App\Models\CostType;
use App\Models\Truck;
use App\Models\TruckExpense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TruckExpense>
 */
class TruckExpenseFactory extends Factory
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
            'expense_date' => fake()->dateTimeBetween('-2 months', 'now'),
            'amount' => fake()->randomFloat(2, 1000, 30000),
            'description' => fake()->sentence(4),
            'created_by' => User::factory(),
        ];
    }
}
