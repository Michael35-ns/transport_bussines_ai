<?php

namespace App\Services\Financial;

use App\Enums\TireEventType;
use App\Models\Tire;
use App\Models\Truck;
use Carbon\CarbonInterface;

/**
 * Amortises a tire's purchase cost evenly over the weeks it has been (or
 * was) mounted, per docs/finance/financial-model.md §J.4. No serials or
 * odometer are tracked (docs/business/discovery.md §L Q23), so this is a
 * straight-line estimate anchored on mount/dismount events, not real wear.
 *
 * A tire whose `current_truck_id` was set without a matching `mount`
 * `TireEvent` contributes nothing — cost estimation needs the event to know
 * when the clock started.
 */
class TireCostEstimator
{
    /**
     * Total tire cost attributable to the truck for the given period.
     */
    public function costFor(Truck $truck, Period $period): float
    {
        $total = 0.0;

        foreach ($this->mountedIntervals($truck, $period) as $interval) {
            $weeklyRate = $interval['weeks_mounted'] > 0
                ? $interval['purchase_cost'] / $interval['weeks_mounted']
                : 0.0;

            $overlapDays = $period->overlapDays($interval['mounted_from'], $interval['mounted_to']);
            $total += $weeklyRate * ($overlapDays / 7);
        }

        return round($total, 2);
    }

    /**
     * @return list<array{purchase_cost: float, mounted_from: CarbonInterface, mounted_to: ?CarbonInterface, weeks_mounted: float}>
     */
    private function mountedIntervals(Truck $truck, Period $period): array
    {
        $intervals = [];

        $tires = Tire::query()
            ->where('current_truck_id', $truck->id)
            ->orWhereHas('tireEvents', fn ($query) => $query->where('truck_id', $truck->id))
            ->with(['tireEvents' => fn ($query) => $query->where('truck_id', $truck->id)->orderBy('occurred_at')])
            ->get();

        foreach ($tires as $tire) {
            $mountedAt = null;

            foreach ($tire->tireEvents as $event) {
                if ($event->event_type === TireEventType::Mount) {
                    $mountedAt = $event->occurred_at;
                }

                if ($event->event_type === TireEventType::Dismount && $mountedAt !== null) {
                    if ($period->overlapDays($mountedAt, $event->occurred_at) > 0) {
                        $intervals[] = $this->interval($tire, $mountedAt, $event->occurred_at);
                    }

                    $mountedAt = null;
                }
            }

            if ($mountedAt !== null && $period->overlapDays($mountedAt, null) > 0) {
                $intervals[] = $this->interval($tire, $mountedAt, null);
            }
        }

        return $intervals;
    }

    /**
     * @return array{purchase_cost: float, mounted_from: CarbonInterface, mounted_to: ?CarbonInterface, weeks_mounted: float}
     */
    private function interval(Tire $tire, CarbonInterface $from, ?CarbonInterface $to): array
    {
        $end = $to ?? now();
        $weeksMounted = max(1, $from->diffInDays($end)) / 7;

        return [
            'purchase_cost' => (float) ($tire->purchase_cost ?? 0),
            'mounted_from' => $from,
            'mounted_to' => $to,
            'weeks_mounted' => $weeksMounted,
        ];
    }
}
