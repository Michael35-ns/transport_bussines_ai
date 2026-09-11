<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 40000, 500000);
        $tax = round($subtotal * 0.13, 2);
        $issueDate = fake()->dateTimeBetween('-2 months', 'now');

        return [
            'customer_id' => Customer::factory(),
            'number' => strtoupper(fake()->unique()->bothify('INV-#####')),
            'issue_date' => $issueDate,
            'due_date' => (clone $issueDate)->modify('+8 days'),
            'currency' => 'CRC',
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => round($subtotal + $tax, 2),
            'status' => InvoiceStatus::Sent,
            'created_by' => User::factory(),
        ];
    }
}
