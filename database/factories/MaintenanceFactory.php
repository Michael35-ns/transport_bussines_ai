<?php

namespace Database\Factories;

use App\Enums\MaintenanceType;
use App\Models\Maintenance;
use App\Models\MaintenanceProvider;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Maintenance>
 */
class MaintenanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $parts = fake()->randomFloat(2, 0, 100000);
        $labor = fake()->randomFloat(2, 0, 50000);
        $other = fake()->randomFloat(2, 0, 10000);

        // completion_date must never precede entry_date — derive it instead
        // of generating both independently.
        $entryDate = fake()->dateTimeBetween('-2 months', '-3 days');
        $completionDate = (clone $entryDate)->modify('+'.fake()->numberBetween(0, 3).' days');

        return [
            'truck_id' => Truck::factory(),
            'type' => fake()->randomElement(MaintenanceType::cases()),
            'cost_type_id' => null,
            'provider_id' => MaintenanceProvider::factory(),
            'schedule_id' => null,
            'entry_date' => $entryDate,
            'completion_date' => $completionDate,
            'odometer' => fake()->randomFloat(2, 0, 300000),
            'description' => fake()->sentence(6),
            'parts_cost' => $parts,
            'labor_cost' => $labor,
            'other_cost' => $other,
            'total' => round($parts + $labor + $other, 2),
            'downtime_days' => fake()->randomFloat(2, 0, 3),
            'created_by' => User::factory(),
        ];
    }
}
