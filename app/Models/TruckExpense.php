<?php

namespace App\Models;

use Database\Factories\TruckExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Ad-hoc cost tied to a truck but not to any specific trip — feeds `OV` in
 * the cost model. Compare with `TripExpense`, which is trip-attributable.
 *
 * @property int $id
 * @property int $truck_id
 * @property int $cost_type_id
 * @property Carbon $expense_date
 * @property string $amount
 * @property string|null $description
 * @property int $created_by
 */
#[Fillable(['truck_id', 'cost_type_id', 'expense_date', 'amount', 'description'])]
class TruckExpense extends Model
{
    /** @use HasFactory<TruckExpenseFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
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

    /**
     * @return BelongsTo<CostType, $this>
     */
    public function costType(): BelongsTo
    {
        return $this->belongsTo(CostType::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
