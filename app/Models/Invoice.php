<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $customer_id
 * @property string $number
 * @property Carbon $issue_date
 * @property Carbon $due_date
 * @property string $currency
 * @property string $subtotal
 * @property string $tax
 * @property string $total
 * @property InvoiceStatus $status
 * @property int $created_by
 */
#[Fillable(['customer_id', 'number', 'issue_date', 'due_date', 'currency', 'subtotal', 'tax', 'total', 'status'])]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'status' => InvoiceStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<InvoiceLine, $this>
     */
    public function invoiceLines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    /**
     * @return HasMany<PaymentAllocation, $this>
     */
    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    /**
     * A payment may settle several invoices — see
     * docs/decisions/0004-payment-allocations.md.
     *
     * @return BelongsToMany<Payment, $this>
     */
    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'payment_allocations')->withPivot('amount');
    }

    /**
     * @return HasMany<InvoiceTrip, $this>
     */
    public function invoiceTrips(): HasMany
    {
        return $this->hasMany(InvoiceTrip::class);
    }

    /**
     * @return BelongsToMany<Trip, $this>
     */
    public function trips(): BelongsToMany
    {
        return $this->belongsToMany(Trip::class, 'invoice_trip');
    }
}
