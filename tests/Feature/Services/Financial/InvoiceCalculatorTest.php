<?php

namespace Tests\Feature\Services\Financial;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Services\Financial\InvoiceCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * IVA is a flat, universal 13% (docs/database/conceptual-model.md item 4) and
 * only paid/partial/overdue are derived — draft/sent are workflow states set
 * by whoever issues the invoice (docs/business/discovery.md §I).
 */
class InvoiceCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_computes_subtotal_tax_and_total_from_the_invoice_lines(): void
    {
        $invoice = Invoice::factory()->create(['subtotal' => 0, 'tax' => 0, 'total' => 0]);
        InvoiceLine::factory()->create(['invoice_id' => $invoice->id, 'amount' => 60000]);
        InvoiceLine::factory()->create(['invoice_id' => $invoice->id, 'amount' => 40000]);

        $result = (new InvoiceCalculator)->recalculate($invoice->fresh());

        $this->assertSame('100000.00', $result->subtotal);
        $this->assertSame('13000.00', $result->tax);
        $this->assertSame('113000.00', $result->total);
    }

    public function test_a_draft_invoice_stays_draft_even_if_fully_paid(): void
    {
        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Draft]);
        InvoiceLine::factory()->create(['invoice_id' => $invoice->id, 'amount' => 100000]);
        $this->allocatePayment($invoice, 113000);

        $result = (new InvoiceCalculator)->recalculate($invoice->fresh());

        $this->assertSame(InvoiceStatus::Draft, $result->status);
    }

    public function test_a_sent_invoice_with_no_payments_and_a_future_due_date_stays_sent(): void
    {
        $invoice = Invoice::factory()->create([
            'status' => InvoiceStatus::Sent,
            'due_date' => now()->addDays(5),
        ]);
        InvoiceLine::factory()->create(['invoice_id' => $invoice->id, 'amount' => 100000]);

        $result = (new InvoiceCalculator)->recalculate($invoice->fresh());

        $this->assertSame(InvoiceStatus::Sent, $result->status);
    }

    public function test_a_sent_invoice_with_no_payments_past_its_due_date_becomes_overdue(): void
    {
        $invoice = Invoice::factory()->create([
            'status' => InvoiceStatus::Sent,
            'due_date' => now()->subDays(1),
        ]);
        InvoiceLine::factory()->create(['invoice_id' => $invoice->id, 'amount' => 100000]);

        $result = (new InvoiceCalculator)->recalculate($invoice->fresh());

        $this->assertSame(InvoiceStatus::Overdue, $result->status);
    }

    public function test_an_invoice_due_today_is_not_yet_overdue(): void
    {
        $invoice = Invoice::factory()->create([
            'status' => InvoiceStatus::Sent,
            'due_date' => now()->startOfDay(),
        ]);
        InvoiceLine::factory()->create(['invoice_id' => $invoice->id, 'amount' => 100000]);

        $result = (new InvoiceCalculator)->recalculate($invoice->fresh());

        $this->assertSame(InvoiceStatus::Sent, $result->status);
    }

    public function test_a_partially_paid_invoice_becomes_partial(): void
    {
        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Sent]);
        InvoiceLine::factory()->create(['invoice_id' => $invoice->id, 'amount' => 100000]);
        $this->allocatePayment($invoice, 50000);

        $result = (new InvoiceCalculator)->recalculate($invoice->fresh());

        $this->assertSame(InvoiceStatus::Partial, $result->status);
    }

    public function test_a_fully_paid_invoice_becomes_paid(): void
    {
        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Sent]);
        InvoiceLine::factory()->create(['invoice_id' => $invoice->id, 'amount' => 100000]);
        $this->allocatePayment($invoice, 113000);

        $result = (new InvoiceCalculator)->recalculate($invoice->fresh());

        $this->assertSame(InvoiceStatus::Paid, $result->status);
    }

    public function test_a_partial_payment_past_the_due_date_stays_partial_not_overdue(): void
    {
        $invoice = Invoice::factory()->create([
            'status' => InvoiceStatus::Sent,
            'due_date' => now()->subDays(10),
        ]);
        InvoiceLine::factory()->create(['invoice_id' => $invoice->id, 'amount' => 100000]);
        $this->allocatePayment($invoice, 20000);

        $result = (new InvoiceCalculator)->recalculate($invoice->fresh());

        $this->assertSame(InvoiceStatus::Partial, $result->status);
    }

    public function test_recalculating_twice_does_not_double_count_payments(): void
    {
        $invoice = Invoice::factory()->create(['status' => InvoiceStatus::Sent]);
        InvoiceLine::factory()->create(['invoice_id' => $invoice->id, 'amount' => 100000]);
        $this->allocatePayment($invoice, 60000);

        $calculator = new InvoiceCalculator;
        $calculator->recalculate($invoice->fresh());
        $result = $calculator->recalculate($invoice->fresh());

        $this->assertSame('113000.00', $result->total);
        $this->assertSame(InvoiceStatus::Partial, $result->status);
    }

    private function allocatePayment(Invoice $invoice, float $amount): void
    {
        PaymentAllocation::factory()->create([
            'invoice_id' => $invoice->id,
            'payment_id' => Payment::factory()->create(['customer_id' => $invoice->customer_id])->id,
            'amount' => $amount,
        ]);
    }
}
