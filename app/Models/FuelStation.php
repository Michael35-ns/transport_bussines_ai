<?php

namespace App\Models;

use Database\Factories\FuelStationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string|null $location
 */
#[Fillable(['name', 'location'])]
class FuelStation extends Model
{
    /** @use HasFactory<FuelStationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return HasMany<FuelRecord, $this>
     */
    public function fuelRecords(): HasMany
    {
        return $this->hasMany(FuelRecord::class);
    }
}
