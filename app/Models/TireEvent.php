<?php

namespace App\Models;

use App\Enums\TireEventType;
use Database\Factories\TireEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $tire_id
 * @property int|null $truck_id
 * @property TireEventType $event_type
 * @property string|null $position
 * @property Carbon $occurred_at
 * @property string|null $notes
 */
#[Fillable(['tire_id', 'truck_id', 'event_type', 'position', 'occurred_at', 'notes'])]
class TireEvent extends Model
{
    /** @use HasFactory<TireEventFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_type' => TireEventType::class,
            'occurred_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Tire, $this>
     */
    public function tire(): BelongsTo
    {
        return $this->belongsTo(Tire::class);
    }

    /**
     * @return BelongsTo<Truck, $this>
     */
    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }
}
