# 0002 — Trip distance source (no odometer capture today)

- **Status:** Accepted (revisit if odometer capture is adopted)
- **Date:** 2026-09-10
- **Deciders:** owner (Discovery §L Q20, Q21), `DataArchitectAgent`, `FinancialAnalystAgent`

## Context

The owner does not record odometer readings today — not per trip, not per fuel stop,
not via GPS ("por nada"). Yet the only defined preventive-maintenance interval (oil
change) is **by kilometres**, and every per-km KPI in the financial model needs a
distance. We cannot invent odometer data, and we will not silently treat missing
distance as zero.

## Decision

1. **Trip distance defaults to `route.standard_km`** for the trip's route. Every
   route must carry a standard distance before trips against it are trusted for
   per-km metrics.
2. `trips` keeps an optional manual `distance` override and a `distance_estimated`
   flag, set `true` whenever the value came from the route default rather than a
   real reading.
3. `trucks.current_odometer` is maintained as an **estimate**: last known anchor
   (from a workshop service, see below) **+** `Σ route.standard_km` of the truck's
   trips since that anchor. This estimate — not a live odometer — drives the
   oil-change schedule's `next_due_odometer`.
4. Workshops record the **actual odometer at every service** (already part of the
   `maintenance` record); each such reading **re-anchors** `trucks.current_odometer`,
   correcting drift from the estimate.
5. `odometer_readings` stays in the schema as the home for any reading that *does*
   get captured (service visits now; trip-level or GPS readings later) but is not
   required for the system to function.
6. Fuel efficiency (km/l vs. baseline) is **deferred** — it needs a real odometer to
   mean anything and would otherwise mislead.

## Consequences

- Every per-km figure (cost/km, revenue/km, profit/km, fuel/maintenance per km) is an
  **estimate** while `distance_estimated = true` on the underlying trips; the
  dashboard must label these clearly, never present them as exact.
- A route with no `standard_km` yet cannot produce per-km figures for its trips; such
  trips are flagged and excluded from per-km aggregates (not silently counted as 0 km).
- Oil-change due dates drift with route-km accuracy between services; the workshop
  reading corrects it every service cycle, bounding the error.
- If the company later adopts per-trip odometer capture (manual entry or GPS), no
  schema change is needed — `trips.distance` simply stops being estimated and
  `odometer_readings` starts filling in from that source.

## Alternatives considered

| Option | Why not (for now) |
|---|---|
| Require odometer entry before a trip can be completed | Contradicts current operating reality (§L Q20); would block the pilot |
| Estimate distance from fuel consumed ÷ baseline km/l | Baseline km/l itself unverified without odometer; less accurate than route standard km |
| Leave distance null and hide per-km KPIs entirely | Defeats the project's core purpose; the route-based estimate is good enough to act on if clearly labelled |
