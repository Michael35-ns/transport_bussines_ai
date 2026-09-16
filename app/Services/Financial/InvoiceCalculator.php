<?php

namespace App\Services\Financial;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;

/**
 * Derives an invoice's totals and status from its own lines and payment
 * allocations — never entered by hand — per
 * docs/business/discovery.md §I "Invoicing & revenue" and
 * docs/finance/financial-model.md's edge cases.
 *
 * IVA is a flat 13%, confirmed universal with no exemptions
 * (docs/database/conceptual-model.md item 4): `total = subtotal + subtotal × 0.13`.
 *
 * `draft`/`sent` are workflow states set by whoever issues the invoice —
 * this never assigns either one. `paid`/`partial`/`overdue` are the only
 * derived states, computed from what's actually been collected.
 *
 * Idempotent: call it after any change to an invoice's lines or payment
 * allocations (creating, editing, or removing either) and it's always safe
 * to re-run — it recomputes and persists from scratch every time, never
 * accumulates.
 */
class InvoiceCalculator
{
    private const IVA_RATE = 0.13;

    public function recalculate(Invoice $invoice): Invoice
    {
        $subtotal = round((float) $invoice->invoiceLines()->sum('amount'), 2);
        $tax = round($subtotal * self::IVA_RATE, 2);
        $total = round($subtotal + $tax, 2);

        $invoice->subtotal = (string) $subtotal;
        $invoice->tax = (string) $tax;
        $invoice->total = (string) $total;
        $invoice->status = $this->resolveStatus($invoice, $total);
        $invoice->save();

        return $invoice;
    }

    private function resolveStatus(Invoice $invoice, float $total): InvoiceStatus
    {
        if ($invoice->status === InvoiceStatus::Draft) {
            return InvoiceStatus::Draft;
        }

        $collected = round((float) $invoice->paymentAllocations()->sum('amount'), 2);

        return match (true) {
            $total > 0 && $collected >= $total => InvoiceStatus::Paid,
            $collected > 0 => InvoiceStatus::Partial,
            now()->startOfDay()->gt($invoice->due_date) => InvoiceStatus::Overdue,
            default => InvoiceStatus::Sent,
        };
    }
}
