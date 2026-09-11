<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\FuelRecord;
use App\Models\FuelStation;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FuelRecord>
 */
class FuelRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $liters = fake()->randomFloat(4, 20, 150);
        $unitPrice = fake()->randomFloat(4, 700, 850);

        return [
            'truck_id' => Truck::factory(),
            'trip_id' => null,
            'fuel_station_id' => FuelStation::factory(),
            'occurred_at' => fake()->dateTimeBetween('-2 months', 'now'),
            'liters' => $liters,
            'unit_price' => $unitPrice,
            'total' => round($liters * $unitPrice, 2),
            'payment_method' => PaymentMethod::Cash,
            'created_by' => User::factory(),
        ];
    }
}
