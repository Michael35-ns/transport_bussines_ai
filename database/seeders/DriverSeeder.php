<?php

namespace Database\Seeders;

use App\Enums\ActiveStatus;
use App\Models\Driver;
use Illuminate\Database\Seeder;

/**
 * The real drivers, as entered by the owner through the Drivers screen
 * (2026-09-16).
 */
class DriverSeeder extends Seeder
{
    /**
     * Seed the drivers.
     */
    public function run(): void
    {
        $drivers = [
            ['name' => 'Cristopher', 'document_id' => '305610278', 'license_class' => 'B2', 'license_expiry' => '2030-09-16', 'hire_date' => '2026-09-02', 'hourly_rate' => 1800, 'status' => ActiveStatus::Active],
            ['name' => 'Iván', 'document_id' => '305680744', 'license_class' => 'B2', 'license_expiry' => '2026-12-16', 'hire_date' => '2026-09-16', 'hourly_rate' => 1800, 'status' => ActiveStatus::Active],
            ['name' => 'Luis', 'document_id' => '303940141', 'license_class' => 'B3', 'license_expiry' => '2029-10-16', 'hire_date' => '2026-09-16', 'hourly_rate' => 1800, 'status' => ActiveStatus::Active],
            ['name' => 'Ronny', 'document_id' => '303840888', 'license_class' => 'B3', 'license_expiry' => '2030-10-16', 'hire_date' => '2026-09-16', 'hourly_rate' => 2000, 'status' => ActiveStatus::Active],
            ['name' => 'Jimmy', 'document_id' => '304330584', 'license_class' => 'B2', 'license_expiry' => '2031-10-16', 'hire_date' => '2022-05-16', 'hourly_rate' => 1800, 'status' => ActiveStatus::Active],
            ['name' => 'Fernando', 'document_id' => '304170021', 'license_class' => 'B1', 'license_expiry' => '2030-05-16', 'hire_date' => '2022-09-16', 'hourly_rate' => 1800, 'status' => ActiveStatus::Active],
            ['name' => 'Carlos', 'document_id' => '104900888', 'license_class' => 'B3', 'license_expiry' => '2031-10-16', 'hire_date' => '2024-01-16', 'hourly_rate' => 1800, 'status' => ActiveStatus::Active],
        ];

        foreach ($drivers as $driver) {
            Driver::query()->updateOrCreate(['document_id' => $driver['document_id']], $driver);
        }
    }
}
