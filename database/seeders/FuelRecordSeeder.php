<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Models\FuelRecord;
use App\Models\FuelStation;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * The real fuel purchases, as entered by the owner through the Combustible
 * screen (2026-09-18). Trucks and fuel stations are matched by their real
 * identifiers (plate/name) rather than hard-coded IDs, since TruckSeeder and
 * FuelStationSeeder must run first but may not assign the same IDs on every
 * environment. `total` is recomputed from liters * unit_price rather than
 * copied, matching how FuelRecords\Index::save() always derives it.
 */
class FuelRecordSeeder extends Seeder
{
    /**
     * Seed the fuel records.
     */
    public function run(): void
    {
        $userId = User::query()->value('id') ?? User::factory()->create()->id;

        $records = [
            ['plate' => 'CL212706', 'station' => 'Gasolinera Coopetarrazu', 'occurred_at' => '2026-09-18 21:26:00', 'liters' => 36.3300, 'unit_price' => 688.0000, 'payment_method' => PaymentMethod::Card],
            ['plate' => 'CL149914', 'station' => 'Gasolinera Coopetarrazu', 'occurred_at' => '2026-09-18 22:02:00', 'liters' => 36.3400, 'unit_price' => 688.0000, 'payment_method' => PaymentMethod::Card],
            ['plate' => 'CL136453', 'station' => 'Gasolinera Guarco', 'occurred_at' => '2026-09-18 22:07:00', 'liters' => 29.0700, 'unit_price' => 688.0000, 'payment_method' => PaymentMethod::Card],
            ['plate' => 'CL312456', 'station' => 'Gasolinera Coopetarrazu', 'occurred_at' => '2026-09-18 22:08:00', 'liters' => 43.6000, 'unit_price' => 688.0000, 'payment_method' => PaymentMethod::Card],
            ['plate' => 'CL328621', 'station' => 'Gasolinera Coopetarrazu', 'occurred_at' => '2026-09-18 22:26:00', 'liters' => 43.6000, 'unit_price' => 688.0000, 'payment_method' => PaymentMethod::Card],
        ];

        foreach ($records as $record) {
            $truck = Truck::query()->where('plate', $record['plate'])->first();
            $station = FuelStation::query()->where('name', $record['station'])->first();

            if ($truck === null || $station === null) {
                continue;
            }

            FuelRecord::query()->updateOrCreate(
                ['truck_id' => $truck->id, 'occurred_at' => $record['occurred_at']],
                [
                    'fuel_station_id' => $station->id,
                    'liters' => $record['liters'],
                    'unit_price' => $record['unit_price'],
                    'total' => round($record['liters'] * $record['unit_price'], 2),
                    'payment_method' => $record['payment_method'],
                    'created_by' => $userId,
                ]
            );
        }
    }
}
