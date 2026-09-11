<?php

namespace Database\Seeders;

use App\Enums\CostTypeScope;
use App\Models\CostType;
use Illuminate\Database\Seeder;

/**
 * Real cost categories from the owner's Discovery answers
 * (docs/business/discovery.md §L Q13).
 */
class CostTypeSeeder extends Seeder
{
    /**
     * Seed the cost types.
     */
    public function run(): void
    {
        $types = [
            // Truck-fixed — direct to a truck, never pooled.
            ['name' => 'Seguro', 'scope' => CostTypeScope::TruckFixed],
            ['name' => 'Permisos', 'scope' => CostTypeScope::TruckFixed],
            ['name' => 'Fumigación', 'scope' => CostTypeScope::TruckFixed],
            ['name' => 'Dekra', 'scope' => CostTypeScope::TruckFixed],
            ['name' => 'Marchamo', 'scope' => CostTypeScope::TruckFixed],

            // Overhead — company-wide pool (docs/decisions/0001).
            ['name' => 'Salarios', 'scope' => CostTypeScope::Overhead],
            ['name' => 'Salarios administrativos', 'scope' => CostTypeScope::Overhead],
            ['name' => 'Cargas sociales', 'scope' => CostTypeScope::Overhead],

            // Ad-hoc truck/trip expenses.
            ['name' => 'Lavado', 'scope' => CostTypeScope::Expense],
            ['name' => 'Multa', 'scope' => CostTypeScope::Expense],
        ];

        foreach ($types as $type) {
            CostType::query()->updateOrCreate(['name' => $type['name']], $type);
        }
    }
}
