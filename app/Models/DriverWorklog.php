<?php

namespace App\Models;

use Database\Factories\DriverWorklogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Feeds the overhead driver-labour pool — drivers are paid hourly and are
 * not attributable to a truck or trip (docs/decisions/0001).
 *
 * @property int $id
 * @property int $driver_id
 * @property Carbon $work_date
 * @property string $hours
 * @property string $hourly_rate_snapshot
 * @property string $computed_pay
 * @property string|null $notes
 * @property int $created_by
 */
#[Fillable(['driver_id', 'work_date', 'hours', 'hourly_rate_snapshot', 'computed_pay', 'notes'])]
class DriverWorklog extends Model
{
    /** @use HasFactory<DriverWorklogFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'hours' => 'decimal:2',
            'hourly_rate_snapshot' => 'decimal:4',
            'computed_pay' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Driver, $this>
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
