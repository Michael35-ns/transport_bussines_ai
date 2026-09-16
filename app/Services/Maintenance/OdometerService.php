<?php

namespace App\Services\Maintenance;

use App\Enums\MaintenanceType;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;

/**
 * Applies the two re-anchoring effects a service-visit odometer reading has,
 * per docs/decisions/0002-trip-distance-source.md and the discovery mermaid
 * flow (Service performed → Record cost + real odometer → Re-anchor
 * current_odometer → back to the schedule):
 *
 * 1. `trucks.current_odometer` is corrected to the truck's most recent known
 *    reading across all its maintenance records — never blindly overwritten
 *    with whatever was just saved — so a backfilled, older record entered
 *    later can never regress a newer anchor.
 * 2. When the maintenance closes out a specific preventive schedule
 *    (`schedule_id` set), that schedule's `last_done_odometer` resets to the
 *    most recent reading recorded against it, so
 *    `MaintenanceSchedule::nextDueOdometer()` counts the next interval from
 *    the service actually performed, not from the truck's ever-increasing
 *    estimate (which would otherwise never reach VENCIDO).
 *
 * Idempotent, like the other services in `app/Services/`: call it again
 * after any change to a Maintenance record's odometer, truck, type, or
 * schedule, and it re-converges from the current data rather than
 * accumulating.
 */
class OdometerService
{
    public function applyReading(Maintenance $maintenance): void
    {
        if ($maintenance->odometer === null) {
            return;
        }

        $this->reanchorTruck($maintenance);
        $this->resetSchedule($maintenance);
    }

    private function reanchorTruck(Maintenance $maintenance): void
    {
        $latest = Maintenance::query()
            ->where('truck_id', $maintenance->truck_id)
            ->whereNotNull('odometer')
            ->orderByDesc('completion_date')
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->value('odometer');

        if ($latest !== null) {
            $maintenance->truck->update(['current_odometer' => $latest]);
        }
    }

    private function resetSchedule(Maintenance $maintenance): void
    {
        if ($maintenance->type !== MaintenanceType::Preventive || $maintenance->schedule_id === null) {
            return;
        }

        $latest = Maintenance::query()
            ->where('schedule_id', $maintenance->schedule_id)
            ->whereNotNull('odometer')
            ->orderByDesc('completion_date')
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->value('odometer');

        if ($latest !== null) {
            MaintenanceSchedule::query()->whereKey($maintenance->schedule_id)->update(['last_done_odometer' => $latest]);
        }
    }
}
