<?php

namespace App\Models;

use App\Enums\OdometerSource;
use Database\Factories\OdometerReadingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Optional / future — no odometer is captured today
 * (docs/decisions/0002-trip-distance-source.md).
 *
 * @property int $id
 * @property int $truck_id
 * @property Carbon $read_at
 * @property string $odometer
 * @property OdometerSource $source
 * @property int|null $source_id
 */
#[Fillable(['truck_id', 'read_at', 'odometer', 'source', 'source_id'])]
class OdometerReading extends Model
{
    /** @use HasFactory<OdometerReadingFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'odometer' => 'decimal:2',
            'source' => OdometerSource::class,
        ];
    }

    /**
     * @return BelongsTo<Truck, $this>
     */
    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }
}
