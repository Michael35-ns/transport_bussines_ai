<?php

namespace App\Services\Financial;

use App\Enums\MaintenanceType;
use App\Enums\TripStatus;
use App\Models\Customer;
use App\Models\Maintenance;
use App\Models\Route;
use App\Models\Trip;
use App\Models\Truck;
use Illuminate\Support\Collection;

/**
 * The financial-calculation engine for the fleet: cost, revenue, profit,
 * margin, and utilization/availability, per docs/finance/financial-model.md.
 * Every per-km figure is an estimate while trip distance defaults from
 * `route.standard_km` (docs/decisions/0002-trip-distance-source.md).
 *
 * Revenue is recognised at trip completion (docs/business/discovery.md §I),
 * so every method here reads `trips` with `status = completed`, never
 * invoices or payments.
 *
 * @phpstan-import-type CostBreakdown from CostCalculator
 *
 * @phpstan-type TruckSummary array{
 *     revenue: float, km: float,
 *     direct_cost: float, indirect_cost: float, total_cost: float,
 *     profit_contribution: float, profit_net: float, margin_pct: ?float,
 *     cost_per_km: ?float, revenue_per_km: ?float, profit_per_km: ?float,
 *     availability_pct: float, utilization_pct: ?float,
 * }
 * @phpstan-type TripCost array{direct_cost: float, allocated_indirect: float, cost: float, profit: float}
 * @phpstan-type GroupProfitability array{revenue: float, direct_cost: float, allocated_indirect: float, profit_contribution: float, profit_net: float, trips: int}
 * @phpstan-type FleetRow array{
 *     truck: Truck, revenue: float, km: float,
 *     direct_cost: float, indirect_cost: float, total_cost: float,
 *     profit_contribution: float, profit_net: float, margin_pct: ?float,
 *     cost_per_km: ?float, revenue_per_km: ?float, profit_per_km: ?float,
 *     availability_pct: float, utilization_pct: ?float,
 * }
 */
class FinancialCalculator
{
    public function __construct(private readonly CostCalculator $costCalculator) {}

    /**
     * @return TruckSummary
     */
    public function truckSummary(Truck $truck, Period $period): array
    {
        $costs = $this->costCalculator->forTruck($truck, $period);
        $trips = $this->completedTripsForTruck($truck, $period);

        $revenue = round((float) $trips->sum('price'), 2);
        $km = round((float) $trips->sum('distance'), 2);

        $profitContribution = round($revenue - $costs['direct_cost'], 2);
        $profitNet = round($revenue - $costs['total_cost'], 2);

        $availability = $this->availability($truck, $period);
        $utilization = $this->utilization($truck, $period, $availability);

        return [
            'revenue' => $revenue,
            'km' => $km,
            'direct_cost' => $costs['direct_cost'],
            'indirect_cost' => $costs['indirect_cost'],
            'total_cost' => $costs['total_cost'],
            'profit_contribution' => $profitContribution,
            'profit_net' => $profitNet,
            'margin_pct' => $revenue > 0 ? round($profitNet / $revenue * 100, 2) : null,
            'cost_per_km' => $km > 0 ? round($costs['total_cost'] / $km, 2) : null,
            'revenue_per_km' => $km > 0 ? round($revenue / $km, 2) : null,
            'profit_per_km' => $km > 0 ? round($profitNet / $km, 2) : null,
            'availability_pct' => round($availability * 100, 2),
            'utilization_pct' => $utilization !== null ? round($utilization * 100, 2) : null,
        ];
    }

    /**
     * Cost and profit for a single trip (docs/finance/financial-model.md
     * §J.7): direct cost is what the trip itself incurred; the indirect
     * share comes from its truck's period, split by that trip's share of
     * the truck's km in the same week.
     *
     * @return TripCost
     */
    public function tripCost(Trip $trip): array
    {
        $period = Period::weekContaining($trip->actual_end ?? $trip->actual_start ?? now());

        $directCost = round(
            (float) $trip->fuelRecords()->sum('total')
            + (float) $trip->tollRecords()->sum('amount')
            + (float) $trip->tripExpenses()->sum('amount'),
            2
        );

        $truckKm = (float) $this->completedTripsForTruck($trip->truck, $period)->sum('distance');
        $truckIndirect = $this->costCalculator->forTruck($trip->truck, $period)['indirect_cost'];

        $allocatedIndirect = $truckKm > 0
            ? round($truckIndirect * ((float) $trip->distance / $truckKm), 2)
            : 0.0;

        $cost = round($directCost + $allocatedIndirect, 2);

        return [
            'direct_cost' => $directCost,
            'allocated_indirect' => $allocatedIndirect,
            'cost' => $cost,
            'profit' => round((float) $trip->price - $cost, 2),
        ];
    }

    /**
     * @return GroupProfitability
     */
    public function routeProfitability(Route $route, Period $period): array
    {
        $trips = Trip::query()
            ->where('route_id', $route->id)
            ->where('status', TripStatus::Completed)
            ->whereBetween('actual_end', [$period->start->copy()->startOfDay(), $period->end->copy()->endOfDay()])
            ->get();

        return $this->summarizeTrips($trips);
    }

    /**
     * A trip belongs to a customer via its rate agreement if it has one,
     * otherwise via the invoice it was billed on. A trip with neither is not
     * yet attributable to any customer and is excluded, not guessed.
     *
     * @return GroupProfitability
     */
    public function customerProfitability(Customer $customer, Period $period): array
    {
        $trips = Trip::query()
            ->where('status', TripStatus::Completed)
            ->whereBetween('actual_end', [$period->start->copy()->startOfDay(), $period->end->copy()->endOfDay()])
            ->with(['rateAgreement', 'invoiceTrip.invoice'])
            ->get()
            ->filter(fn (Trip $trip) => $this->resolveTripCustomerId($trip) === $customer->id);

        return $this->summarizeTrips($trips);
    }

    /**
     * One row per truck. A plain list rather than a `Collection` — the
     * array-shape-in-collection generic doesn't type-check cleanly under
     * Larastan, and a list is all a dashboard/report consumer needs here.
     *
     * @return list<FleetRow>
     */
    public function fleetSummary(Period $period): array
    {
        return array_map(function (Truck $truck) use ($period) {
            $summary = $this->truckSummary($truck, $period);

            return [
                'truck' => $truck,
                'revenue' => $summary['revenue'],
                'km' => $summary['km'],
                'direct_cost' => $summary['direct_cost'],
                'indirect_cost' => $summary['indirect_cost'],
                'total_cost' => $summary['total_cost'],
                'profit_contribution' => $summary['profit_contribution'],
                'profit_net' => $summary['profit_net'],
                'margin_pct' => $summary['margin_pct'],
                'cost_per_km' => $summary['cost_per_km'],
                'revenue_per_km' => $summary['revenue_per_km'],
                'profit_per_km' => $summary['profit_per_km'],
                'availability_pct' => $summary['availability_pct'],
                'utilization_pct' => $summary['utilization_pct'],
            ];
        }, array_values(Truck::query()->get()->all()));
    }

    /**
     * Only breakdowns/corrective repairs count against availability
     * (docs/business/discovery.md §I) — planned/preventive service does not.
     */
    public function availability(Truck $truck, Period $period): float
    {
        $downtimeDays = (float) Maintenance::query()
            ->where('truck_id', $truck->id)
            ->where('type', MaintenanceType::Corrective)
            ->whereBetween('completion_date', [$period->start->toDateString(), $period->end->toDateString()])
            ->sum('downtime_days');

        $availableDays = max(0.0, $period->days() - $downtimeDays);

        return $period->days() > 0 ? $availableDays / $period->days() : 0.0;
    }

    /**
     * `worked_days / available_days` within the period. Null when the truck
     * has zero available days (fully down).
     */
    public function utilization(Truck $truck, Period $period, ?float $availability = null): ?float
    {
        $availability ??= $this->availability($truck, $period);
        $availableDays = $availability * $period->days();

        if ($availableDays <= 0) {
            return null;
        }

        $workedDays = $this->completedTripsForTruck($truck, $period)
            ->map(fn (Trip $trip) => ($trip->actual_start ?? $trip->actual_end)->toDateString())
            ->unique()
            ->count();

        return $workedDays / $availableDays;
    }

    /**
     * @return Collection<int, Trip>
     */
    private function completedTripsForTruck(Truck $truck, Period $period): Collection
    {
        return Trip::query()
            ->where('truck_id', $truck->id)
            ->where('status', TripStatus::Completed)
            ->whereBetween('actual_end', [$period->start->copy()->startOfDay(), $period->end->copy()->endOfDay()])
            ->get();
    }

    private function resolveTripCustomerId(Trip $trip): ?int
    {
        if ($trip->rateAgreement !== null) {
            return $trip->rateAgreement->customer_id;
        }

        return $trip->invoiceTrip?->invoice?->customer_id;
    }

    /**
     * @param  Collection<int, Trip>  $trips
     * @return GroupProfitability
     */
    private function summarizeTrips(Collection $trips): array
    {
        $revenue = 0.0;
        $directCost = 0.0;
        $allocatedIndirect = 0.0;

        foreach ($trips as $trip) {
            $revenue += (float) $trip->price;
            $tripCost = $this->tripCost($trip);
            $directCost += $tripCost['direct_cost'];
            $allocatedIndirect += $tripCost['allocated_indirect'];
        }

        $totalCost = $directCost + $allocatedIndirect;

        return [
            'revenue' => round($revenue, 2),
            'direct_cost' => round($directCost, 2),
            'allocated_indirect' => round($allocatedIndirect, 2),
            'profit_contribution' => round($revenue - $directCost, 2),
            'profit_net' => round($revenue - $totalCost, 2),
            'trips' => $trips->count(),
        ];
    }
}
