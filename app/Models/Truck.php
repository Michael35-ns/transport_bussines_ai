<?php

namespace App\Models;

use App\Enums\AcquisitionMode;
use App\Enums\ActiveStatus;
use App\Enums\VehicleType;
use Database\Factories\TruckFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $plate
 * @property string|null $internal_no
 * @property VehicleType $vehicle_type
 * @property string|null $make
 * @property string|null $model
 * @property int|null $year
 * @property Carbon|null $acquisition_date
 * @property AcquisitionMode $acquisition_mode
 * @property string|null $financing_monthly
 * @property string $current_odometer
 * @property ActiveStatus $status
 * @property string|null $base_yard
 */
#[Fillable([
    'plate', 'internal_no', 'vehicle_type', 'make', 'model', 'year',
    'acquisition_date', 'acquisition_mode', 'financing_monthly',
    'current_odometer', 'status', 'base_yard',
])]
class Truck extends Model
{
    /** @use HasFactory<TruckFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'vehicle_type' => VehicleType::class,
            'acquisition_date' => 'date',
            'acquisition_mode' => AcquisitionMode::class,
            'financing_monthly' => 'decimal:2',
            'current_odometer' => 'decimal:2',
            'status' => ActiveStatus::class,
        ];
    }

    /**
     * @return HasMany<Trip, $this>
     */
    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    /**
     * @return HasMany<FuelRecord, $this>
     */
    public function fuelRecords(): HasMany
    {
        return $this->hasMany(FuelRecord::class);
    }

    /**
     * @return HasMany<TollRecord, $this>
     */
    public function tollRecords(): HasMany
    {
        return $this->hasMany(TollRecord::class);
    }

    /**
     * @return HasMany<OdometerReading, $this>
     */
    public function odometerReadings(): HasMany
    {
        return $this->hasMany(OdometerReading::class);
    }

    /**
     * @return HasMany<MaintenanceSchedule, $this>
     */
    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    /**
     * @return HasMany<Maintenance, $this>
     */
    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    /**
     * @return HasMany<Tire, $this>
     */
    public function tires(): HasMany
    {
        return $this->hasMany(Tire::class, 'current_truck_id');
    }

    /**
     * @return HasMany<TireEvent, $this>
     */
    public function tireEvents(): HasMany
    {
        return $this->hasMany(TireEvent::class);
    }

    /**
     * @return HasMany<TruckExpense, $this>
     */
    public function truckExpenses(): HasMany
    {
        return $this->hasMany(TruckExpense::class);
    }

    /**
     * @return HasMany<TruckFixedCost, $this>
     */
    public function truckFixedCosts(): HasMany
    {
        return $this->hasMany(TruckFixedCost::class);
    }

    /**
     * @return HasMany<FixedCostAllocation, $this>
     */
    public function fixedCostAllocations(): HasMany
    {
        return $this->hasMany(FixedCostAllocation::class);
    }
}
