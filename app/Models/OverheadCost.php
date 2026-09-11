<?php

namespace App\Models;

use App\Enums\BillingCycle;
use Database\Factories\OverheadCostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Company-wide cost (salaries, social charges, …) — the pool allocated to
 * trucks per docs/decisions/0001-overhead-allocation-method.md.
 *
 * @property int $id
 * @property int $cost_type_id
 * @property string $amount
 * @property BillingCycle $billing_cycle
 * @property Carbon $effective_from
 * @property Carbon|null $effective_to
 */
#[Fillable(['cost_type_id', 'amount', 'billing_cycle', 'effective_from', 'effective_to'])]
class OverheadCost extends Model
{
    /** @use HasFactory<OverheadCostFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'billing_cycle' => BillingCycle::class,
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    /**
     * @return BelongsTo<CostType, $this>
     */
    public function costType(): BelongsTo
    {
        return $this->belongsTo(CostType::class);
    }
}
