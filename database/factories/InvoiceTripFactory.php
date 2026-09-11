<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceTrip;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceTrip>
 */
class InvoiceTripFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'trip_id' => Trip::factory(),
        ];
    }
}
