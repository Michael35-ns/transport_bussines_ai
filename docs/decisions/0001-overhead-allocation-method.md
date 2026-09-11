# 0001 — Overhead (indirect cost) allocation method

- **Status:** Accepted (pending final per-truck cost figures)
- **Date:** 2026-09-10
- **Deciders:** owner (via Discovery §L Q13, Q14, Q17), `FinancialAnalystAgent`
- **Supersedes:** the "active-days ≈ equal" recommendation in the first draft of
  `docs/finance/financial-model.md`

## Context

The owner wants overhead split **"por camión y actividad realizada por el camión"**
(per truck, by the activity each truck performed). Company-level overhead is currently
**unknown and never measured** — capturing it is a goal of the system. The overhead
pool includes: employee salaries, administrative salaries, and social charges (*cargas
sociales*); driver pay belongs here because drivers are paid **by the hour for daily
hours worked** and change from trip to trip, so their cost cannot be attributed to a
single trip or truck.

## Decision

1. **Per-truck fixed costs** (insurance, permits, *fumigación*, *dekra*, *marchamo*)
   are assigned **directly** to the truck — never pooled.
2. **True overhead** (all salaries + social charges + office) forms a weekly pool
   `Overhead(P)`.
3. The pool is allocated to each truck by an **activity share**, default:
   `weight(t,P) = worked_days(t,P) / Σ_t worked_days(t,P)`,
   where `worked_days` = days in the period on which the truck ran at least one trip.
4. The weight basis is **configurable** per period: `worked_days` (default), `trips`
   share, or `km` share.
5. Each period's method and per-truck weights and amounts are **persisted** in
   `fixed_cost_allocations` so any historical dashboard figure can be reproduced.
6. The dashboard always shows **two views**: *contribution* (`revenue − direct cost`,
   allocation-free) and *net* (`revenue − total cost`, includes allocated overhead).

## Consequences

- An idle truck in a week still carries **zero** overhead under `worked_days`; this is
  acceptable because the owner's question is "which trucks earn their keep", and a
  truck that did not run also earned nothing — the contribution view already exposes it.
  Revisit if the owner wants idle trucks to absorb a standby share.
- Requires a weekly driver-hours capture (`driver_worklogs`) to size the driver portion
  of the pool.
- Changing the basis later re-computes cleanly for any period; raw data is untouched.

## Alternatives considered

| Option | Why not (for now) |
|---|---|
| By kilometres | Penalises long routes; and km is itself estimated (see ADR 0002) |
| By revenue | Circular when overhead informs pricing; flat-rate pricing already varies |
| Equal per truck | Contradicts the owner's explicit "by activity performed" |
| Full ABC / cost pools | No pool-level data yet; revisit once overhead is actually measured |
