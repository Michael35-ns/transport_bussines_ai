# 0004 — Payments can settle more than one invoice

- **Status:** Accepted and implemented (2026-09-11)
- **Date:** 2026-09-11
- **Deciders:** owner (Discovery §L.2 #9), `DataArchitectAgent`

## Context

`payments.invoice_id` (built in the initial schema) assumed one payment settles
exactly one invoice. The owner confirmed a single payment **can** cover more than
one invoice. The 1:1 model no longer reflects reality and must change before any
real payment data is entered.

## Decision

1. `payments` becomes a record of a **customer transaction**: `customer_id`,
   `paid_at`, `amount` (the full amount received), `method`, `reference`. It no
   longer references an invoice directly.
2. A new `payment_allocations` table records how a payment's amount is split
   across invoices: `payment_id`, `invoice_id`, `amount`. A payment with one
   allocation behaves exactly like the old 1:1 case; a payment with several
   allocations covers several invoices.
3. `Σ payment_allocations.amount` for a payment must not exceed that payment's
   `amount` — enforced at the application layer (service/action), not the database,
   since the check spans rows.
4. `invoices.status` (`paid`/`partial`/`overdue`) is computed from
   `Σ payment_allocations.amount` for that invoice, same as before — just sourced
   from the allocation table instead of a direct `payments` sum.

## Consequences

- `Payment` model loses its `invoice()` relation, gains `customer()` and
  `allocations()`; `Invoice` gains `payments()` as a `belongsToMany` through
  `payment_allocations` instead of a plain `hasMany`.
- Recording a payment becomes a two-step write (the payment, then one or more
  allocations) instead of one row — the natural place for this is a
  `RecordPayment` action once the app layer exists, not raw model creation.
- No real payment data exists yet, so this is a clean schema change with nothing
  to migrate forward.

## Migration plan (pending go-ahead)

- **Affected files:** new migration `create_payment_allocations_table`; alter
  migration on `payments` (drop `invoice_id`, add `customer_id`); `app/Models/Payment.php`;
  new `app/Models/PaymentAllocation.php`; `app/Models/Invoice.php` (`payments()`
  relation); `database/factories/PaymentFactory.php` (rewrite); new
  `database/factories/PaymentAllocationFactory.php`.
- **Risk:** none today — `payments` has no real rows (only factory-generated rows in
  tests, which get regenerated). If real payments existed, each existing
  `payments.invoice_id` would need one `payment_allocations` row for the full
  amount before dropping the column.
- **Rollback:** drop `payment_allocations`, re-add `payments.invoice_id`, restore
  the old model relations. Trivial while no real data exists.

## Alternatives considered

| Option | Why not |
|---|---|
| Keep `payments.invoice_id`, add a second nullable `invoice_id_2` etc. | Doesn't generalise past 2 invoices; not normalized |
| Keep 1:1 and require the customer to make one payment per invoice | Contradicts the owner's confirmed practice |
