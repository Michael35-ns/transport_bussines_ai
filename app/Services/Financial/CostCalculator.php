<?php

namespace App\Services\Financial;

use App\Models\FuelRecord;
use App\Models\Maintenance;
use App\Models\TollRecord;
use App\Models\Truck;
use App\Models\TruckExpense;
use App\Models\TruckFixedCost;

/**
 * Direct and indirect cost breakdown for a truck over a period, per
 * docs/finance/financial-model.md §J.1. There is no capital-cost
 * (depreciation / financing) line — the owner explicitly omitted it.
 *
 * @phpstan-type CostBreakdown array{
 *     fuel: float, tolls: float, maintenance: float, tires: float,
 *     truck_expenses: float, direct_cost: float,
 *     truck_fixed_costs: float, allocated_overhead: float, indirect_cost: float,
 *     total_cost: float,
 * }
 */
class CostCalculator
{
    public function __construct(
        private readonly TireCostEstimator $tireCostEstimator,
        private readonly OverheadAllocationService $overheadAllocation,
        private readonly ProrationCalculator $proration,
    ) {}

    /**
     * @return CostBreakdown
     */
    public function forTruck(Truck $truck, Period $period): array
    {
        $start = $period->start->copy()->startOfDay();
        $end = $period->end->copy()->endOfDay();

        $fuel = (float) FuelRecord::query()
            ->where('truck_id', $truck->id)
            ->whereBetween('occurred_at', [$start, $end])
            ->sum('total');

        $tolls = (float) TollRecord::query()
            ->where('truck_id', $truck->id)
            ->whereBetween('occurred_at', [$start, $end])
            ->sum('amount');

        $maintenance = (float) Maintenance::query()
            ->where('truck_id', $truck->id)
            ->whereBetween('completion_date', [$period->start->toDateString(), $period->end->toDateString()])
            ->sum('total');

        $tires = $this->tireCostEstimator->costFor($truck, $period);

        $truckExpenses = (float) TruckExpense::query()
            ->where('truck_id', $truck->id)
            ->whereBetween('expense_date', [$period->start->toDateString(), $period->end->toDateString()])
            ->sum('amount');

        $directCost = round($fuel + $tolls + $maintenance + $tires + $truckExpenses, 2);

        $truckFixedCosts = TruckFixedCost::query()
            ->where('truck_id', $truck->id)
            ->where('effective_from', '<=', $period->end->toDateString())
            ->where(function ($query) use ($period) {
                $query->whereNull('effective_to')->orWhere('effective_to', '>=', $period->start->toDateString());
            })
            ->get()
            ->sum(fn (TruckFixedCost $cost) => $this->proration->toPeriod((float) $cost->amount, $cost->billing_cycle, $period));

        $allocatedOverhead = $this->overheadAllocation->allocatedAmount($truck, $period);

        $indirectCost = round($truckFixedCosts + $allocatedOverhead, 2);

        return [
            'fuel' => round($fuel, 2),
            'tolls' => round($tolls, 2),
            'maintenance' => round($maintenance, 2),
            'tires' => round($tires, 2),
            'truck_expenses' => round($truckExpenses, 2),
            'direct_cost' => $directCost,
            'truck_fixed_costs' => round($truckFixedCosts, 2),
            'allocated_overhead' => round($allocatedOverhead, 2),
            'indirect_cost' => $indirectCost,
            'total_cost' => round($directCost + $indirectCost, 2),
        ];
    }
}
