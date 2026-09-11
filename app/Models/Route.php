<?php

namespace App\Models;

use Database\Factories\RouteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $origin
 * @property string $destination
 * @property string $standard_km
 * @property string|null $typical_toll_cost
 * @property bool $is_round_trip
 */
#[Fillable(['name', 'origin', 'destination', 'standard_km', 'typical_toll_cost', 'is_round_trip'])]
class Route extends Model
{
    /** @use HasFactory<RouteFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'standard_km' => 'decimal:2',
            'typical_toll_cost' => 'decimal:2',
            'is_round_trip' => 'boolean',
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
     * @return HasMany<RateAgreement, $this>
     */
    public function rateAgreements(): HasMany
    {
        return $this->hasMany(RateAgreement::class);
    }
}
