<?php

namespace Database\Factories;

use App\Enums\AcquisitionMode;
use App\Enums\ActiveStatus;
use App\Enums\VehicleType;
use App\Models\Truck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Truck>
 */
class TruckFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plate' => strtoupper(fake()->unique()->bothify('###???')),
            'internal_no' => fake()->unique()->numerify('U-##'),
            'vehicle_type' => fake()->randomElement(VehicleType::cases()),
            'make' => fake()->randomElement(['Freightliner', 'Isuzu', 'Hino', 'Kenworth']),
            'model' => fake()->bothify('Model-##'),
            'year' => fake()->numberBetween(2012, 2025),
            'acquisition_date' => fake()->dateTimeBetween('-8 years', '-1 year'),
            'acquisition_mode' => fake()->randomElement(AcquisitionMode::cases()),
            'financing_monthly' => null,
            'current_odometer' => fake()->randomFloat(2, 0, 300000),
            'status' => ActiveStatus::Active,
            'base_yard' => fake()->city(),
        ];
    }
}
