<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Database\Factories\FuelRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $truck_id
 * @property int|null $trip_id
 * @property int $fuel_station_id
 * @property Carbon $occurred_at
 * @property string $liters
 * @property string $unit_price
 * @property string $total
 * @property PaymentMethod $payment_method
 * @property int $created_by
 */
#[Fillable(['truck_id', 'trip_id', 'fuel_station_id', 'occurred_at', 'liters', 'unit_price', 'total', 'payment_method'])]
class FuelRecord extends Model
{
    /** @use HasFactory<FuelRecordFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'liters' => 'decimal:4',
            'unit_price' => 'decimal:4',
            'total' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
        ];
    }

    /**
     * @return BelongsTo<Truck, $this>
     */
    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }

    /**
     * @return BelongsTo<Trip, $this>
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * @return BelongsTo<FuelStation, $this>
     */
    public function fuelStation(): BelongsTo
    {
        return $this->belongsTo(FuelStation::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
