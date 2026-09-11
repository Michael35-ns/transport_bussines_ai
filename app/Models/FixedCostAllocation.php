<?php

namespace App\Models;

use App\Enums\AllocationMethod;
use Database\Factories\FixedCostAllocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * The audit trail behind docs/decisions/0001-overhead-allocation-method.md —
 * one row per truck per computed weekly period, never hand-edited.
 *
 * @property int $id
 * @property int $truck_id
 * @property Carbon $period_start
 * @property Carbon $period_end
 * @property AllocationMethod $method
 * @property string $weight
 * @property string $amount
 */
#[Fillable(['truck_id', 'period_start', 'period_end', 'method', 'weight', 'amount'])]
class FixedCostAllocation extends Model
{
    /** @use HasFactory<FixedCostAllocationFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'method' => AllocationMethod::class,
            'weight' => 'decimal:6',
            'amount' => 'decimal:2',
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
