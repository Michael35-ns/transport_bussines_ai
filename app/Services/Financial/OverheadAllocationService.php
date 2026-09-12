<?php

namespace App\Services\Financial;

use App\Enums\AllocationMethod;
use App\Enums\TripStatus;
use App\Models\DriverWorklog;
use App\Models\FixedCostAllocation;
use App\Models\OverheadCost;
use App\Models\Trip;
use App\Models\Truck;
use Illuminate\Support\Collection;

/**
 * Computes and persists the overhead pool split across trucks per
 * docs/decisions/0001-overhead-allocation-method.md — activity-based, using
 * each truck's share of worked days by default. Truck-specific fixed costs
 * are never pooled here; see `CostCalculator` for those.
 *
 * Idempotent: re-running for the same period overwrites that period's
 * allocation rows with the freshly computed split, so it's always safe to
 * call before reading `allocatedAmount()`.
 */
class OverheadAllocationService
{
    public function __construct(private readonly ProrationCalculator $proration) {}

    /**
     * Allocate the period's overhead pool across every truck and persist it.
     *
     * @return Collection<int, FixedCostAllocation>
     */
    public function allocate(Period $period): Collection
    {
        $pool = $this->overheadPool($period);
        $trucks = Truck::query()->get();

        $workedDays = $trucks->mapWithKeys(
            fn (Truck $truck) => [$truck->id => $this->workedDays($truck, $period)]
        );
        $totalWorkedDays = $workedDays->sum();

        return $trucks->map(function (Truck $truck) use ($pool, $workedDays, $totalWorkedDays, $period) {
            $weight = $totalWorkedDays > 0 ? $workedDays[$truck->id] / $totalWorkedDays : 0.0;

            return FixedCostAllocation::query()->updateOrCreate(
                [
                    'truck_id' => $truck->id,
                    'period_start' => $period->start->toDateString(),
                    'period_end' => $period->end->toDateString(),
                ],
                [
                    'method' => AllocationMethod::WorkedDays,
                    'weight' => round($weight, 6),
                    'amount' => round($pool * $weight, 2),
                ]
            );
        });
    }

    /**
     * The amount allocated to this truck for this period. Runs `allocate()`
     * first so the figure always reflects the current pool and split.
     */
    public function allocatedAmount(Truck $truck, Period $period): float
    {
        $this->allocate($period);

        return (float) (FixedCostAllocation::query()
            ->where('truck_id', $truck->id)
            ->where('period_start', $period->start->toDateString())
            ->where('period_end', $period->end->toDateString())
            ->value('amount') ?? 0);
    }

    private function overheadPool(Period $period): float
    {
        $costs = OverheadCost::query()
            ->where('effective_from', '<=', $period->end->toDateString())
            ->where(function ($query) use ($period) {
                $query->whereNull('effective_to')->orWhere('effective_to', '>=', $period->start->toDateString());
            })
            ->get()
            ->sum(fn (OverheadCost $cost) => $this->proration->toPeriod((float) $cost->amount, $cost->billing_cycle, $period));

        $driverPay = (float) DriverWorklog::query()
            ->whereBetween('work_date', [$period->start->toDateString(), $period->end->toDateString()])
            ->sum('computed_pay');

        return $costs + $driverPay;
    }

    private function workedDays(Truck $truck, Period $period): int
    {
        return Trip::query()
            ->where('truck_id', $truck->id)
            ->where('status', TripStatus::Completed)
            ->whereBetween('actual_end', [$period->start->copy()->startOfDay(), $period->end->copy()->endOfDay()])
            ->get(['actual_start', 'actual_end'])
            ->map(fn (Trip $trip) => ($trip->actual_start ?? $trip->actual_end)->toDateString())
            ->unique()
            ->count();
    }
}
