<?php

namespace App\Models;

use App\Enums\BillingCycle;
use Database\Factories\TruckFixedCostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * The real billed amount and cycle; weekly figures are derived by proration
 * (docs/finance/financial-model.md §J.3), never stored here.
 *
 * @property int $id
 * @property int $truck_id
 * @property int $cost_type_id
 * @property string $amount
 * @property BillingCycle $billing_cycle
 * @property Carbon $effective_from
 * @property Carbon|null $effective_to
 */
#[Fillable(['truck_id', 'cost_type_id', 'amount', 'billing_cycle', 'effective_from', 'effective_to'])]
class TruckFixedCost extends Model
{
    /** @use HasFactory<TruckFixedCostFactory> */
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
}
