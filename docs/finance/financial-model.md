# Financial model — cost, profitability and KPIs

**Status:** DRAFT for owner validation. **Owner agent:** `FinancialAnalystAgent`.
Depends on answers to [`docs/business/discovery.md`](../business/discovery.md) §L.
The chosen overhead-allocation method will be recorded as a decision in
[`docs/decisions/`](../decisions/).

All money is stored as `DECIMAL(15,2)`; rates (per km, per litre, per hour) as
`DECIMAL(12,4)`; distances as `DECIMAL(10,2)` km. Rounding (half-up, 2 dp) happens only
at presentation. Every figure below must be drillable to the source rows that produced it.

---

## Notation

For a period `P` (default: calendar month) and a truck `t`:

| Symbol | Meaning |
|---|---|
| `km(t,P)` | Σ `trip_distance` for trips of `t` completed in `P` |
| `hours(t,P)` | Σ actual trip duration in `P` (or engine hours if available) |
| `days(P)` | calendar days in `P` |
| `available_days(t,P)` | `days(P) − downtime_days(t,P)` |
| `revenue_days(t,P)` | distinct days `t` ran a revenue trip in `P` |

---

## Cost taxonomy — direct vs indirect

| Class | Definition | Items |
|---|---|---|
| **Direct** | Traceable to a single trip (or truck) with no allocation | Fuel, tolls, per-trip / per-km driver pay, trip expenses; a truck's own maintenance & tire wear |
| **Truck-fixed** | Assignable to one truck but not to a trip; period-based | Insurance, permits, financing/lease payment or depreciation, telematics, parking |
| **Indirect / overhead** | Shared across the operation; needs an allocation rule | Admin & management salaries, office rent, dispatch software, shared insurance, company financing, utilities |

> Classification note: "truck-fixed" costs are **direct to the truck** but **indirect to
> a trip**. Trip-level profitability therefore spreads truck-fixed + overhead across the
> truck's trips (by km share by default); truck-level profitability charges truck-fixed
> directly and only allocates true overhead.

---

## J. Formulas

### J.1 Cost components (per truck, per period)

| Component | Formula | Source |
|---|---|---|
| Fuel `F` | Σ `fuel_records.total` | `fuel_records` |
| Tolls `TL` | Σ `toll_records.amount` | `toll_records` |
| Maintenance `M` | Σ `maintenance.total` with `completion_date ∈ P` | `maintenance` |
| Tires `TR` | Σ tire amortisation in `P` (J.4) | `tires`, `tire_events` |
| Driver `D` | per-trip / per-km driver pay for `t`'s trips in `P` (0 if scheme is not attributable → falls into overhead) | `drivers`, `trips` |
| Other variable `OV` | Σ `truck_expenses.amount` in `P` | `truck_expenses` |
| Truck-fixed `FX` | Σ `truck_fixed_costs` prorated to `P` | `truck_fixed_costs` |
| Capital `CAP` | depreciation **or** financing payment (J.3) — never both | `trucks` / `truck_fixed_costs` |
| Allocated overhead `IND` | `Overhead(P) × weight(t,P)` (see §Allocation) | `overhead_costs`, `fixed_cost_allocations` |

```
DirectCost   DC(t,P) = F + TL + M + TR + D + OV
IndirectCost IC(t,P) = FX + CAP + IND
TotalCost    TC(t,P) = DC(t,P) + IC(t,P)
```

### J.2 Per-unit cost

```
cost_per_km(t,P)   = TC(t,P) / km(t,P)
cost_per_hour(t,P) = TC(t,P) / hours(t,P)
cost_per_day(t,P)  = TC(t,P) / days(P)
monthly_cost(t)    = TC(t, month)
daily_cost(t)      = monthly_cost(t) / days(month)

fuel_cost_per_km(t,P)        = F(t,P)  / km(t,P)
maintenance_cost_per_km(t,P) = (M(t,P) + TR(t,P)) / km(t,P)
```

Guard: if `km(t,P) = 0` (or `hours`, `days`), the per-unit metric is **undefined** and
shown as "—", not `0` and not a division error.

### J.3 Capital cost (choose one, per truck — §L Q9)

```
depreciation(t,P) = (acquisition_cost − residual_value) / useful_life_months × months(P)
financing(t,P)    = Σ financing/lease payments due in P
```

### J.4 Tire amortisation (choose one — §L Q23)

```
# usage-based, preferred when dismount odometer is known
tire_cost_per_km(tire) = (purchase_cost + Σ retread_cost) / (dismount_odo − mount_odo)
TR(t,P) = Σ over tires mounted on t during P of  tire_cost_per_km(tire) × km_on_t_in_P

# straight-line fallback while a tire is still mounted
TR(t,P) = Σ (purchase_cost / expected_life_km) × km(t,P) share per mounted tire
```

### J.5 Revenue

```
revenue(t,P)        = Σ trip.price for t's trips recognised in P   (recognition point per §L Q2)
revenue_per_km(t,P) = revenue(t,P) / km(t,P)
revenue_per_trip    = trip.price
```

### J.6 Profit & margin

```
profit(t,P)        = revenue(t,P) − TC(t,P)
profit_per_km(t,P) = profit(t,P) / km(t,P)          # = revenue_per_km − cost_per_km
margin_pct(t,P)    = profit(t,P) / revenue(t,P) × 100

contribution_margin(trip i) = trip.price − DC(i)    # for accept/reject pricing
```

### J.7 Cost & profit per trip

```
DC(i)                = fuel(i) + tolls(i) + trip_expenses(i) + driver_pay(i)
allocated_IC(i)      = IC(t,P) × (km_i / km(t,P))          # default: by km share
cost_per_trip(i)     = DC(i) + allocated_IC(i)
profit_per_trip(i)   = trip.price(i) − cost_per_trip(i)
```

Alternative `allocated_IC(i)` bases: by trip count (`1 / trips(t,P)`) or by revenue
share (`revenue_i / revenue(t,P)`). Default is km share; configurable.

### J.8 Profitability by dimension (period `P`)

```
by trip i      : trip.price(i) − cost_per_trip(i)
by route r     : Σ_{i on r} (price_i − DC_i)  −  Σ_{i on r} allocated_IC(i)
by customer c  : Σ_{i of c} (price_i − DC_i)  −  Σ_{i of c} allocated_IC(i)  − customer_specific_costs(c,P)
by truck t     : profit(t,P)                                  (J.6)
```

`customer_specific_costs` (optional, phase ≥ Profitability): e.g. financing cost of
long receivables = `overdue_balance(c) × cost_of_capital × days/365`.

### J.9 Fleet aggregates

```
fleet_cost_per_km(P)    = Σ_t TC(t,P) / Σ_t km(t,P)
fleet_revenue_per_km(P) = Σ_t revenue(t,P) / Σ_t km(t,P)
fleet_margin_pct(P)     = (Σ_t revenue − Σ_t TC) / Σ_t revenue × 100

availability(t,P) = available_days(t,P) / days(P)
utilization(t,P)  = revenue_days(t,P) / available_days(t,P)     # default; owner may redefine (§L Q16)
```

---

## Overhead allocation — alternatives (do not assume)

`weight(t,P)` distributes `Overhead(P)` across trucks; `Σ_t weight(t,P) = 1`.

| # | Method | `weight(t,P)` | Pros | Cons |
|---|---|---|---|---|
| 1 | By kilometres | `km(t,P) / Σ km` | Tracks road usage; simple; defensible | Penalises long-haul low-margin lanes; **idle trucks absorb ≈ 0 overhead** |
| 2 | By revenue | `revenue(t,P) / Σ revenue` | "Ability to pay"; ties overhead to earnings | Circular when overhead feeds pricing; high-rate lanes over-absorb |
| 3 | By active days (≈ equal per truck) | `available_days(t,P) / Σ available_days` | Overhead is mostly the cost of *having* the operation (capacity); idle trucks still carry cost | Ignores intensity of use |
| 4 | By direct-cost share | `DC(t,P) / Σ DC` | Proxy for total activity | Fuel-price swings distort it |
| 5 | Hybrid / ABC-lite | Split overhead into pools (dispatch → by trips; billing/admin → by invoice count; management → equal; yard → per truck) and allocate each pool by its own driver | Most accurate; audit-friendly | Needs pool data + configuration |

### Recommendation

**Start with method 3 (active-days share)** for capacity-type overhead, and keep
truck-fixed costs assigned directly to each truck (not pooled). Expose an admin setting
to switch to **method 5 (hybrid)** once the owner can break overhead into pools.

Rationale: the business question is "should we keep / add this truck?", and overhead is
largely the cost of running the operation at all, not of any single kilometre. A
days-based split does not punish long-haul lanes and does not let an idle truck escape
its share of overhead — which is exactly the signal the owner needs.

**Always show two views** so decisions are not hostage to the allocation choice:

- **Contribution view** — `revenue − DirectCost` only (allocation-free).
- **Net view** — `revenue − TotalCost` including allocated overhead.

Persist the chosen method and the resulting per-truck weights per period in
`fixed_cost_allocations` so any past dashboard number can be reproduced exactly.

---

## K. KPIs

Grain: F = fleet, T = truck, R = route, C = customer. Period selectable (default month).

| KPI | Formula | Grain | Source tables |
|---|---|---|---|
| Billing / revenue | `Σ trip.price` (recognised) | F,T,R,C | trips, invoices, invoice_trip |
| Total cost | `TC` (J.1) | F,T | fuel, toll, maintenance, tires, expenses, fixed, overhead |
| Direct cost | `DC` (J.1) | F,T,R,C | fuel, toll, trip_expenses, drivers |
| Indirect cost | `IC` (J.1) | F,T | truck_fixed_costs, overhead_costs, fixed_cost_allocations |
| Profit | `revenue − TC` | F,T,R,C | (above) |
| Margin % | `profit / revenue × 100` | F,T,R,C | (above) |
| Cost / km | `TC / km` | F,T | + odometer_readings / trips |
| Revenue / km | `revenue / km` | F,T,R | trips |
| Profit / km | `profit / km` | F,T,R | (above) |
| Fuel cost / km | `F / km` | F,T | fuel_records, trips |
| Maintenance cost / km | `(M+TR) / km` | F,T | maintenance, tires, trips |
| Cost / hour | `TC / hours` | T | trips, maintenance… |
| Cost / day | `TC / days` | T | (above) |
| Cost / trip | `cost_per_trip(i)` (J.7) | T,R,C | (above) |
| Utilization % | `revenue_days / available_days` | T,F | trips, maintenance |
| Availability % | `available_days / days` | T,F | maintenance |
| Km driven | `km(t,P)` | F,T,R | trips / odometer_readings |
| Trips count | `count(trips)` | F,T,R,C | trips |
| Avg revenue / trip | `revenue / trips count` | F,T,R,C | trips |
| Profitability ranking | `profit` sorted asc | T,R,C | (above) |
| Maintenance overdue | `count(schedule where status = VENCIDO)` | F,T | maintenance_schedule |
| Maintenance upcoming | `count(schedule where status = PROXIMO)` | F,T | maintenance_schedule |
| Downtime days | `Σ downtime` | F,T | maintenance |
| Invoiced / collected / overdue | `Σ invoice.total` / `Σ payments` / `Σ overdue balance` | F,C | invoices, payments |
| DSO *(optional)* | `Σ AR / (revenue / days)` | F,C | invoices, payments |
| Fuel efficiency km/l *(optional)* | `km / litres` vs `trucks.baseline_km_l` | F,T | fuel_records, trips |

Every KPI card links to the source rows (trip ids, fuel-record ids, maintenance ids …)
that produced the number.

---

## Edge cases the engine (and its tests) must handle

- Truck with **zero km / zero trips** in the period → per-km metrics show "—", overhead
  still allocated if method 3 (active days) and the truck was available.
- **Missing / estimated odometer** → distance from `route.standard_km`, flagged; metrics
  labelled "includes estimates".
- **Cost recorded mid-period** (e.g. insurance change) → proration by day.
- **Maintenance spanning two periods** → cost attributed by `completion_date`
  (accrual variant: split by day — decision, §L).
- **Partial payments / one payment across invoices** → collection KPIs use allocated
  amounts, not invoice totals.
- **Trip billed in a later period than completed** → revenue in completion period
  (accrual) unless owner chooses cash basis (§L Q2).
- **Tire still mounted at period end** → straight-line amortisation (J.4 fallback).
