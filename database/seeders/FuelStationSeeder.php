<?php

namespace Database\Seeders;

use App\Models\FuelStation;
use Illuminate\Database\Seeder;

/**
 * The real fuel stations, as entered by the owner through the Estaciones
 * screen (2026-09-16).
 */
class FuelStationSeeder extends Seeder
{
    /**
     * Seed the fuel stations.
     */
    public function run(): void
    {
        $stations = [
            ['name' => 'Gasolinera Coopetarrazu', 'location' => 'Bajo del Río, Tarrazu'],
            ['name' => 'Gasolinera Guarco', 'location' => 'Guarco, Cartago'],
            ['name' => 'Gasolinera Muca', 'location' => 'San Pablo, León Cortés'],
        ];

        foreach ($stations as $station) {
            FuelStation::query()->updateOrCreate(['name' => $station['name']], $station);
        }
    }
}
