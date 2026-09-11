<?php

namespace App\Models;

use App\Enums\MaintenanceIntervalType;
use Database\Factories\MaintenanceScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Status (AL_DIA / PROXIMO / VENCIDO) is intentionally not a stored column —
 * it is computed against `truck.current_odometer` so it can never drift from
 * the estimate that feeds it (docs/database/physical model, `maintenance_schedule`).
 *
 * @property int $id
 * @property int $truck_id
 * @property string $task_name
 * @property MaintenanceIntervalType $interval_type
 * @property string|null $interval_value
 * @property string|null $last_done_odometer
 * @property string $lead_km
 * @property bool $active
 */
#[Fillable(['truck_id', 'task_name', 'interval_type', 'interval_value', 'last_done_odometer', 'lead_km', 'active'])]
class MaintenanceSchedule extends Model
{
    /** @use HasFactory<MaintenanceScheduleFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'interval_type' => MaintenanceIntervalType::class,
            'interval_value' => 'decimal:2',
            'last_done_odometer' => 'decimal:2',
            'lead_km' => 'decimal:2',
            'active' => 'boolean',
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
     * @return HasMany<Maintenance, $this>
     */
    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class, 'schedule_id');
    }

    /**
     * The km at which this task next falls due, based on the truck's
     * maintained odometer estimate. Null when the task is not km-based or
     * the truck relation is not loaded.
     */
    public function nextDueOdometer(): ?float
    {
        if ($this->interval_type !== MaintenanceIntervalType::Km || $this->interval_value === null) {
            return null;
        }

        $baseline = $this->last_done_odometer ?? $this->truck?->current_odometer;

        return $baseline === null ? null : (float) $baseline + (float) $this->interval_value;
    }

    /**
     * `AL_DIA`, `PROXIMO`, or `VENCIDO` per the business rule in
     * docs/business/discovery.md §I.
     */
    public function status(): string
    {
        $dueAt = $this->nextDueOdometer();
        $current = (float) ($this->truck->current_odometer ?? 0);

        if ($dueAt === null) {
            return 'AL_DIA';
        }

        if ($current >= $dueAt) {
            return 'VENCIDO';
        }

        if ($current >= $dueAt - (float) $this->lead_km) {
            return 'PROXIMO';
        }

        return 'AL_DIA';
    }
}
