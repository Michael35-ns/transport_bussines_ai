<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

/**
 * The real customers, as entered by the owner through the Customers screen
 * (2026-09-16).
 */
class CustomerSeeder extends Seeder
{
    /**
     * Seed the customers.
     */
    public function run(): void
    {
        $customers = [
            ['name' => 'Coopetarrazu', 'tax_id' => '12141314141', 'credit_days' => 8, 'contact' => '8172218319'],
        ];

        foreach ($customers as $customer) {
            Customer::query()->updateOrCreate(['name' => $customer['name']], $customer);
        }
    }
}
