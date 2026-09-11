<?php

namespace App\Models;

use App\Enums\CostTypeScope;
use Database\Factories\CostTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property CostTypeScope $scope
 */
#[Fillable(['name', 'scope'])]
class CostType extends Model
{
    /** @use HasFactory<CostTypeFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scope' => CostTypeScope::class,
        ];
    }

    /**
     * @return HasMany<TruckExpense, $this>
     */
    public function truckExpenses(): HasMany
    {
        return $this->hasMany(TruckExpense::class);
    }

    /**
     * @return HasMany<TripExpense, $this>
     */
    public function tripExpenses(): HasMany
    {
        return $this->hasMany(TripExpense::class);
    }

    /**
     * @return HasMany<TruckFixedCost, $this>
     */
    public function truckFixedCosts(): HasMany
    {
        return $this->hasMany(TruckFixedCost::class);
    }

    /**
     * @return HasMany<OverheadCost, $this>
     */
    public function overheadCosts(): HasMany
    {
        return $this->hasMany(OverheadCost::class);
    }
}
