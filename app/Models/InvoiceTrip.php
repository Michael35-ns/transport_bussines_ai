<?php

namespace App\Models;

use Database\Factories\InvoiceTripFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $invoice_id
 * @property int $trip_id
 */
#[Fillable(['invoice_id', 'trip_id'])]
class InvoiceTrip extends Model
{
    /** @use HasFactory<InvoiceTripFactory> */
    use HasFactory;

    protected $table = 'invoice_trip';

    /**
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return BelongsTo<Trip, $this>
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}
