<?php

namespace Database\Seeders;

use App\Models\Route;
use Illuminate\Database\Seeder;

/**
 * The real routes, as entered by the owner through the Routes screen
 * (2026-09-16 to 2026-09-18). standard_km/typical_toll_cost are the owner's
 * own measurements, not estimated (docs/decisions/0002-trip-distance-source.md).
 */
class RouteSeeder extends Seeder
{
    /**
     * Seed the routes.
     */
    public function run(): void
    {
        $routes = [
            ['name' => 'Ruta Monterrey', 'origin' => 'Casa', 'destination' => 'Casa', 'standard_km' => 53.00, 'typical_toll_cost' => null, 'is_round_trip' => false],
            ['name' => 'Ruta Cartago', 'origin' => 'Casa', 'destination' => 'Casa', 'standard_km' => 75.50, 'typical_toll_cost' => null, 'is_round_trip' => false],
            ['name' => 'Ruta Llano Bonito', 'origin' => 'Casa', 'destination' => 'Casa', 'standard_km' => 45.20, 'typical_toll_cost' => null, 'is_round_trip' => false],
            ['name' => 'Ruta San Marcos', 'origin' => 'Casa', 'destination' => 'Casa', 'standard_km' => 32.40, 'typical_toll_cost' => null, 'is_round_trip' => false],
            ['name' => 'Ruta Dota', 'origin' => 'Casa', 'destination' => 'Casa', 'standard_km' => 38.70, 'typical_toll_cost' => null, 'is_round_trip' => false],
            ['name' => 'Walmart - Dota', 'origin' => 'Casa', 'destination' => 'Coyol, Alajuela', 'standard_km' => 59.30, 'typical_toll_cost' => 5510.00, 'is_round_trip' => false],
            ['name' => 'Walmart - Buen Día', 'origin' => 'Casa', 'destination' => 'Coyol, Alajuela', 'standard_km' => 59.30, 'typical_toll_cost' => 5510.00, 'is_round_trip' => false],
            ['name' => 'Ruta Corralillo', 'origin' => 'Casa', 'destination' => 'San Marcos', 'standard_km' => 60.00, 'typical_toll_cost' => null, 'is_round_trip' => false],
            ['name' => 'Bobinas - Tarimas', 'origin' => 'Coyol', 'destination' => 'San Marcos', 'standard_km' => 108.00, 'typical_toll_cost' => null, 'is_round_trip' => false],
            ['name' => 'Verdura', 'origin' => 'Casa', 'destination' => 'Casa', 'standard_km' => 35.00, 'typical_toll_cost' => null, 'is_round_trip' => false],
        ];

        foreach ($routes as $route) {
            Route::query()->updateOrCreate(['name' => $route['name']], $route);
        }
    }
}
