<?php

namespace App\Models;

use Database\Factories\PaymentAllocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The slice of a payment applied to one invoice — see
 * docs/decisions/0004-payment-allocations.md. `Σ amount` across a payment's
 * allocations must not exceed that payment's own `amount` (enforced by the
 * service/action that records payments, not the database).
 *
 * @property int $id
 * @property int $payment_id
 * @property int $invoice_id
 * @property string $amount
 */
#[Fillable(['payment_id', 'invoice_id', 'amount'])]
class PaymentAllocation extends Model
{
    /** @use HasFactory<PaymentAllocationFactory> */
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
        ];
    }

    /**
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
