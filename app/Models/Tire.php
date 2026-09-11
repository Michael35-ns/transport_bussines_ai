<?php

namespace App\Models;

use App\Enums\TireStatus;
use Database\Factories\TireFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * No serial numbers or kilometres are tracked today — position rotation and
 * visual-wear replacement only (docs/business/discovery.md §L Q23).
 *
 * @property int $id
 * @property string $label
 * @property TireStatus $status
 * @property int|null $current_truck_id
 * @property string|null $current_position
 * @property string|null $purchase_cost
 * @property int|null $provider_id
 */
#[Fillable(['label', 'status', 'current_truck_id', 'current_position', 'purchase_cost', 'provider_id'])]
class Tire extends Model
{
    /** @use HasFactory<TireFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TireStatus::class,
            'purchase_cost' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Truck, $this>
     */
    public function currentTruck(): BelongsTo
    {
        return $this->belongsTo(Truck::class, 'current_truck_id');
    }

    /**
     * @return BelongsTo<MaintenanceProvider, $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(MaintenanceProvider::class, 'provider_id');
    }

    /**
     * @return HasMany<TireEvent, $this>
     */
    public function tireEvents(): HasMany
    {
        return $this->hasMany(TireEvent::class);
    }
}
