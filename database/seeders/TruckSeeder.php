<?php

namespace Database\Seeders;

use App\Enums\AcquisitionMode;
use App\Enums\ActiveStatus;
use App\Enums\VehicleType;
use App\Models\Truck;
use Illuminate\Database\Seeder;

/**
 * The real fleet, as entered by the owner through the Trucks screen
 * (2026-09-16 to 2026-09-18).
 */
class TruckSeeder extends Seeder
{
    /**
     * Seed the trucks.
     */
    public function run(): void
    {
        $trucks = [
            [
                'plate' => 'CL312456', 'internal_no' => 'rojo pequeño', 'vehicle_type' => VehicleType::FurgonSeco,
                'make' => 'Isuzu', 'model' => 'NPR', 'year' => 2008, 'acquisition_date' => '2026-09-16',
                'acquisition_mode' => AcquisitionMode::Financed, 'financing_monthly' => 350000,
                'current_odometer' => 550000, 'status' => ActiveStatus::Active, 'base_yard' => 'Casa/Previo',
            ],
            [
                'plate' => 'CL136453', 'internal_no' => 'azul', 'vehicle_type' => VehicleType::FurgonSeco,
                'make' => 'Isuzu', 'model' => 'NKR', 'year' => 1998, 'acquisition_date' => '2026-09-16',
                'acquisition_mode' => AcquisitionMode::Owned, 'financing_monthly' => null,
                'current_odometer' => 650000, 'status' => ActiveStatus::Active, 'base_yard' => 'Casa/Previo',
            ],
            [
                'plate' => 'C170063', 'internal_no' => 'rojo grande', 'vehicle_type' => VehicleType::FurgonSeco,
                'make' => 'Isuzu', 'model' => 'FRR', 'year' => 2006, 'acquisition_date' => '2026-09-16',
                'acquisition_mode' => AcquisitionMode::Owned, 'financing_monthly' => null,
                'current_odometer' => 700000, 'status' => ActiveStatus::Active, 'base_yard' => 'Casa/Previo',
            ],
            [
                'plate' => 'CL203733', 'internal_no' => 'frontier', 'vehicle_type' => VehicleType::Pickup,
                'make' => 'Nissan', 'model' => 'Frontier', 'year' => 1999, 'acquisition_date' => '2026-09-16',
                'acquisition_mode' => AcquisitionMode::Owned, 'financing_monthly' => null,
                'current_odometer' => 560000, 'status' => ActiveStatus::Active, 'base_yard' => 'Casa/Previo',
            ],
            [
                'plate' => 'CL328621', 'internal_no' => 'automático', 'vehicle_type' => VehicleType::FurgonSeco,
                'make' => 'Isuzu', 'model' => 'NPR', 'year' => 2008, 'acquisition_date' => '2026-09-16',
                'acquisition_mode' => AcquisitionMode::Owned, 'financing_monthly' => null,
                'current_odometer' => 650000, 'status' => ActiveStatus::Active, 'base_yard' => 'Casa/Previo',
            ],
            [
                'plate' => 'CL212706', 'internal_no' => 'manzanita', 'vehicle_type' => VehicleType::FurgonSeco,
                'make' => 'Isuzu', 'model' => 'NPR', 'year' => 2011, 'acquisition_date' => '2026-09-16',
                'acquisition_mode' => AcquisitionMode::Owned, 'financing_monthly' => null,
                'current_odometer' => 750000, 'status' => ActiveStatus::Active, 'base_yard' => 'Casa/Previo',
            ],
            [
                'plate' => 'CL149914', 'internal_no' => 'blanco', 'vehicle_type' => VehicleType::Pickup,
                'make' => 'Isuzu', 'model' => 'NQR', 'year' => 2004, 'acquisition_date' => '2026-07-08',
                'acquisition_mode' => AcquisitionMode::Owned, 'financing_monthly' => null,
                'current_odometer' => 650000, 'status' => ActiveStatus::Active, 'base_yard' => 'casa',
            ],
        ];

        foreach ($trucks as $truck) {
            Truck::query()->updateOrCreate(['plate' => $truck['plate']], $truck);
        }
    }
}
