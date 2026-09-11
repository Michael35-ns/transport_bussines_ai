<?php

namespace App\Models;

use App\Enums\MaintenanceType;
use Database\Factories\MaintenanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $truck_id
 * @property MaintenanceType $type
 * @property int|null $cost_type_id
 * @property int $provider_id
 * @property int|null $schedule_id
 * @property Carbon $entry_date
 * @property Carbon|null $completion_date
 * @property string|null $odometer
 * @property string|null $description
 * @property string $parts_cost
 * @property string $labor_cost
 * @property string $other_cost
 * @property string $total
 * @property string $downtime_days
 * @property int $created_by
 */
#[Fillable([
    'truck_id', 'type', 'cost_type_id', 'provider_id', 'schedule_id', 'entry_date',
    'completion_date', 'odometer', 'description', 'parts_cost', 'labor_cost',
    'other_cost', 'total', 'downtime_days',
])]
class Maintenance extends Model
{
    /** @use HasFactory<MaintenanceFactory> */
    use HasFactory;

    protected $table = 'maintenance';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MaintenanceType::class,
            'entry_date' => 'date',
            'completion_date' => 'date',
            'odometer' => 'decimal:2',
            'parts_cost' => 'decimal:2',
            'labor_cost' => 'decimal:2',
            'other_cost' => 'decimal:2',
            'total' => 'decimal:2',
            'downtime_days' => 'decimal:2',
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
     * @return BelongsTo<CostType, $this>
     */
    public function costType(): BelongsTo
    {
        return $this->belongsTo(CostType::class);
    }

    /**
     * @return BelongsTo<MaintenanceProvider, $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(MaintenanceProvider::class, 'provider_id');
    }

    /**
     * @return BelongsTo<MaintenanceSchedule, $this>
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(MaintenanceSchedule::class, 'schedule_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
