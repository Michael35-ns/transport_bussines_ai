# 0003 — Weekly reporting period

- **Status:** Accepted, boundary confirmed 2026-09-11
- **Date:** 2026-09-10
- **Deciders:** owner (Discovery §L Q24, §L.2 #6), `FinancialAnalystAgent`, `LaravelArchitectAgent`

## Context

The owner reports **every Thursday**, not on a calendar-month cycle. All prior drafts
of the financial model and dashboard assumed a monthly period `P`.

## Decision

1. The system's primary period is a **week**, running **Thursday → Wednesday**,
   reported on Thursday. Confirmed by the owner (discovery.md §L.2 #6): the week
   starts Thursday and ends the following Wednesday.
2. Periods are modelled as an explicit `period_start` / `period_end` date range
   everywhere (`fixed_cost_allocations`, dashboard filters, KPI queries) — **not** a
   `YYYY-MM` string.
3. Monthly and annual views are **aggregations** of weeks (sum weeks whose range falls
   in the calendar month/year), computed on demand — not a separately stored period.
4. Fixed costs billed monthly, quarterly, or annually (insurance, *fumigación*,
   *dekra*, *marchamo*) are **prorated to the week** by day-count:
   `weekly_amount = period_amount × 7 / days_in(period_amount's billing cycle)`.
5. The dashboard defaults to the current week plus a trailing-weeks trend, with a
   month/quarter/year roll-up view available.

## Consequences

- Every period-bound table and query uses a date range, which also makes ad-hoc
  ranges (e.g. "last 4 weeks") free — no redesign needed later.
- Proration introduces small rounding differences against the actual monthly bill;
  acceptable since the source `truck_fixed_costs` / `overhead_costs` row keeps the
  real billing amount and cycle — the weekly figure is a derived view, not the
  transaction of record.
- Utilization/availability (`worked_days / available_days`, ADR affecting availability
  is Discovery §I) are computed **within the 7-day window** by default.

## Alternatives considered

| Option | Why not |
|---|---|
| Keep monthly as primary, weekly as a secondary view | Inverts the owner's actual operating cadence; monthly would need its own retrofitted date-range logic anyway |
| Store a `period` as `YYYY-Www` (ISO week) label | Less flexible than an explicit date range for month/quarter roll-ups and ad-hoc ranges; range is a superset |
