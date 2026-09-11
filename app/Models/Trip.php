<?php

namespace App\Models;

use App\Enums\TripStatus;
use Database\Factories\TripFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $truck_id
 * @property int $driver_id
 * @property int $route_id
 * @property int|null $rate_agreement_id
 * @property Carbon|null $planned_start
 * @property Carbon|null $actual_start
 * @property Carbon|null $actual_end
 * @property string|null $distance
 * @property bool $distance_estimated
 * @property string|null $price
 * @property TripStatus $status
 * @property int $created_by
 */
#[Fillable([
    'truck_id', 'driver_id', 'route_id', 'rate_agreement_id', 'planned_start',
    'actual_start', 'actual_end', 'distance', 'distance_estimated', 'price', 'status',
])]
class Trip extends Model
{
    /** @use HasFactory<TripFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'planned_start' => 'datetime',
            'actual_start' => 'datetime',
            'actual_end' => 'datetime',
            'distance' => 'decimal:2',
            'distance_estimated' => 'boolean',
            'price' => 'decimal:2',
            'status' => TripStatus::class,
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
     * @return BelongsTo<Driver, $this>
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * @return BelongsTo<Route, $this>
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * @return BelongsTo<RateAgreement, $this>
     */
    public function rateAgreement(): BelongsTo
    {
        return $this->belongsTo(RateAgreement::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
     * @return HasMany<TripExpense, $this>
     */
    public function tripExpenses(): HasMany
    {
        return $this->hasMany(TripExpense::class);
    }

    /**
     * @return HasOne<InvoiceTrip, $this>
     */
    public function invoiceTrip(): HasOne
    {
        return $this->hasOne(InvoiceTrip::class);
    }
}
