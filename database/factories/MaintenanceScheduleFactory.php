<?php

namespace Database\Factories;

use App\Enums\MaintenanceIntervalType;
use App\Models\MaintenanceSchedule;
use App\Models\Truck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceSchedule>
 */
class MaintenanceScheduleFactory extends Factory
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
            'task_name' => 'Cambio de aceite',
            'interval_type' => MaintenanceIntervalType::Km,
            'interval_value' => 10000,
            'last_done_odometer' => fake()->randomFloat(2, 0, 200000),
            'lead_km' => 500,
            'active' => true,
        ];
    }
}
