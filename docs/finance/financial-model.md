# Financial model — cost, profitability and KPIs

**Status:** Updated with owner answers (2026-09-10). **Owner agent:**
`FinancialAnalystAgent`. Builds on
[`docs/business/discovery.md`](../business/discovery.md); the overhead-allocation
method, the distance source, and the reporting period are fixed by
[ADR 0001](../decisions/0001-overhead-allocation-method.md),
[ADR 0002](../decisions/0002-trip-distance-source.md) and
[ADR 0003](../decisions/0003-weekly-reporting-period.md) respectively.

Money: `DECIMAL(15,2)`, currency **CRC**, IVA **13%** flat (pending confirmation of
exemptions, §L.2 #10). Rates `DECIMAL(12,4)`; distances `DECIMAL(10,2)` km. Rounding
(half-up, 2 dp) only at presentation. Every figure is drillable to its source rows.

---

## Notation

Period `P` is a **week** by default (Thursday → Wednesday, ADR 0003); month/quarter/
year are aggregations of weeks. For truck `t`:

| Symbol | Meaning |
|---|---|
| `km(t,P)` | Σ `trip.distance` (= route standard km unless overridden, ADR 0002) for `t`'s trips completed in `P` |
| `worked_days(t,P)` | distinct days in `P` on which `t` ran ≥ 1 completed trip |
| `available_days(t,P)` | `days(P) − corrective_downtime_days(t,P)` |
| `days(P)` | 7 by default (calendar days of the window for other ranges) |

---

## Cost taxonomy

| Class | Definition | Items |
|---|---|---|
| **Direct** | Traceable to a single trip / truck, no allocation | Fuel, tolls, trip-linked expenses, that truck's own maintenance & tire wear |
| **Truck-fixed** | Assignable to one truck, not pooled, various billing cycles | Insurance (theft/rollover), permits, *fumigación* (quarterly), *dekra* (annual), *marchamo* (annual) |
| **Overhead (pooled)** | Company-wide; needs the allocation in ADR 0001 | Employee salaries **(including driver hours × rate)**, administrative salaries, social charges (*cargas sociales*), office |

There is **no capital-cost line** (depreciation / financing) — the owner explicitly
omitted it. Driver pay is **not** direct (it is hourly, general, not trip-attributable)
— it sits entirely inside overhead.

---

## J. Formulas

### J.1 Cost components (per truck, per period)

| Component | Formula | Source |
|---|---|---|
| Fuel `F` | Σ `fuel_records.total` | `fuel_records` |
| Tolls `TL` | Σ `toll_records.amount` | `toll_records` (cash, manual) |
| Maintenance `M` | Σ `maintenance.total` with `completion_date ∈ P` | `maintenance` |
| Tires `TR` | Σ tire cost apportioned to `t` in `P` (J.4) | `tires`, `tire_events` |
| Other variable `OV` | Σ `truck_expenses.amount` in `P` | `truck_expenses` |
| Truck-fixed `FX` | Σ `truck_fixed_costs` **prorated to the week** (J.3) | `truck_fixed_costs` |
| Allocated overhead `IND` | `Overhead(P) × weight(t,P)` (ADR 0001) | `overhead_costs`, `driver_worklogs`, `fixed_cost_allocations` |

```
DirectCost   DC(t,P) = F + TL + M + TR + OV
IndirectCost IC(t,P) = FX + IND                 # no capital-cost term
TotalCost    TC(t,P) = DC(t,P) + IC(t,P)
```

### J.2 Per-unit cost

```
cost_per_km(t,P)   = TC(t,P) / km(t,P)          # "—" if km(t,P) = 0; distance is an estimate (ADR 0002)
cost_per_day(t,P)  = TC(t,P) / days(P)
weekly_cost(t)     = TC(t, week)
monthly_cost(t)    = Σ weekly_cost(t) for weeks overlapping the month

fuel_cost_per_km(t,P)        = F(t,P) / km(t,P)
maintenance_cost_per_km(t,P) = (M(t,P) + TR(t,P)) / km(t,P)
```

`cost_per_hour` is **not** computed for now — there is no reliable per-truck hours
figure (driver hours are general, not tied to a truck, §L.2 #5); revisit if that
changes.

### J.3 Truck-fixed cost proration (ADR 0003)

```
weekly_amount = billing_amount × 7 / days_in(billing_cycle)
  where days_in(monthly)   ≈ 30.44
        days_in(quarterly) ≈ 91.31   (fumigación)
        days_in(annual)    = 365     (dekra, marchamo)
```

The transaction of record keeps the real billing amount and cycle
(`truck_fixed_costs.amount` / `.period`); the weekly figure is a derived view.

### J.4 Tire cost

No serials or odometer are tracked (owner: position rotation + visual-wear
replacement). Until real usage data exists, apportion each tire's cost evenly across
the weeks it stays mounted, split across the trucks it served that week if moved
(rare). Revisit as a straight-usage model if the company later tracks tire km.

```
TR(t,P) = Σ over tires mounted on t during P of (tire cost ÷ estimated weeks mounted)
```

### J.5 Revenue

```
revenue(t,P)        = Σ trip.price for t's trips completed in P     # recognition = trip completion
revenue_per_km(t,P) = revenue(t,P) / km(t,P)
revenue_per_trip     = trip.price                                    # flat rate
```

### J.6 Profit & margin

```
profit(t,P)        = revenue(t,P) − TC(t,P)
profit_per_km(t,P) = profit(t,P) / km(t,P)
margin_pct(t,P)     = profit(t,P) / revenue(t,P) × 100

contribution_margin(trip i) = trip.price − DC(i)      # allocation-free, for pricing calls
```

### J.7 Cost & profit per trip

```
DC(i)              = fuel(i) + tolls(i) + trip_expenses(i)        # no driver pay here
allocated_IC(i)     = IC(t,P) × (km_i / km(t,P))                  # default split: by km within the truck's own trips
cost_per_trip(i)    = DC(i) + allocated_IC(i)
profit_per_trip(i)  = trip.price(i) − cost_per_trip(i)
```

### J.8 Profitability by dimension (period `P`)

```
by trip i      : trip.price(i) − cost_per_trip(i)
by route r     : Σ_{i on r} (price_i − DC_i) − Σ_{i on r} allocated_IC(i)
by customer c  : Σ_{i of c} (price_i − DC_i) − Σ_{i of c} allocated_IC(i)
by truck t     : profit(t,P)                                       (J.6)
```

### J.9 Fleet aggregates

```
fleet_cost_per_km(P)    = Σ_t TC(t,P) / Σ_t km(t,P)
fleet_revenue_per_km(P) = Σ_t revenue(t,P) / Σ_t km(t,P)
fleet_margin_pct(P)     = (Σ_t revenue − Σ_t TC) / Σ_t revenue × 100

availability(t,P) = available_days(t,P) / days(P)          # only corrective downtime counts
utilization(t,P)  = worked_days(t,P) / available_days(t,P)
```

---

## Overhead allocation (ADR 0001)

**Decided:** activity-based, default weight `worked_days(t,P) / Σ_t worked_days(t,P)`,
configurable to `trips` or `km` share. Truck-fixed costs are never pooled. The overhead
pool includes `Σ driver_worklogs.hours × hourly_rate` — the driver-labour cost. Method
and resulting weights are persisted per period in `fixed_cost_allocations`.

The dashboard always shows two views so decisions are not hostage to the allocation:

- **Contribution view** — `revenue − DirectCost` only.
- **Net view** — `revenue − TotalCost`, including allocated overhead.

See ADR 0001 for the alternatives considered and why they were rejected for now.

---

## K. KPIs

Grain: F = fleet, T = truck, R = route, C = customer. Default period = **week**; a
month/quarter/year roll-up sums the covering weeks.

| KPI | Formula | Grain | Notes |
|---|---|---|---|
| Billing / revenue | `Σ trip.price` at completion | F,T,R,C | |
| Total cost | `TC` (J.1) | F,T | |
| Direct cost | `DC` (J.1) | F,T,R,C | |
| Indirect cost | `IC` (J.1) | F,T | truck-fixed + allocated overhead |
| Profit (contribution / net) | `revenue − DC` / `revenue − TC` | F,T,R,C | always show both |
| Margin % | `profit / revenue × 100` | F,T,R,C | |
| Cost / km | `TC / km` | F,T | **estimated** (ADR 0002) |
| Revenue / km | `revenue / km` | F,T,R | estimated |
| Profit / km | `profit / km` | F,T,R | estimated |
| Fuel cost / km | `F / km` | F,T | estimated |
| Maintenance cost / km | `(M+TR) / km` | F,T | estimated |
| Cost / day | `TC / days(P)` | T | |
| Cost / trip | `cost_per_trip(i)` (J.7) | T,R,C | |
| Utilization % | `worked_days / available_days` | T,F | |
| Availability % | `available_days / days(P)` | T,F | corrective downtime only |
| Km driven | `km(t,P)` | F,T,R | estimated |
| Trips count | `count(trips)` | F,T,R,C | |
| Avg revenue / trip | `revenue / trips count` | F,T,R,C | |
| Profitability ranking | `profit` sorted asc | T,R,C | |
| Maintenance overdue / upcoming | `count(schedule = VENCIDO / PROXIMO)` | F,T | oil change only, for now |
| Downtime days | `Σ corrective downtime` | F,T | |
| Invoiced / collected / overdue | `Σ invoice.total` / `Σ payments` / `Σ overdue balance` | F,C | 15-day accounts flagged past ~8-day typical slip |
| Fuel efficiency km/l | — | — | **deferred**, no reliable odometer (ADR 0002) |

Every KPI card links to the source rows that produced the number.

---

## Edge cases the engine (and its tests) must handle

- Truck with **zero trips** in the week → per-km metrics show "—"; it still may
  receive an overhead share if `worked_days = 0` reduces its weight to 0 (no trips ⇒
  no allocation under the default basis).
- **Route with no `standard_km`** → its trips are excluded from per-km aggregates and
  flagged, never counted as 0 km.
- **Truck-fixed cost billed mid-week/mid-month** (e.g. insurance renewal) → proration
  by day (J.3), not a lump sum in one week.
- **Maintenance spanning the week boundary** → cost attributed by `completion_date`.
- **Partial payment** → invoice status `partial`; collection KPIs use amounts actually
  received.
- **15-day-credit invoice paid ~8 days late** → still counted as revenue at trip
  completion; only the collections/overdue KPI is affected.
- **Driver worklog missing for a day** → that day's labour cost is absent from the
  overhead pool for that period (flag as a data-quality gap, don't estimate it).
- **IVA on an exempt customer/trip** (if §L.2 #10 confirms exemptions exist) → tax
  becomes conditional per invoice line, not a hardcoded 13%.
