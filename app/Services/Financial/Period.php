<?php

namespace App\Services\Financial;

use Carbon\CarbonInterface;

/**
 * An explicit date range, inclusive of both ends — periods are never a
 * `YYYY-MM` string (docs/decisions/0003-weekly-reporting-period.md).
 *
 * Accepts `CarbonInterface` rather than a concrete Carbon class since
 * `AppServiceProvider` switches the app's date class to `CarbonImmutable`.
 */
final class Period
{
    public function __construct(
        public readonly CarbonInterface $start,
        public readonly CarbonInterface $end,
    ) {}

    /**
     * The Thursday -> Wednesday week (the project's default period,
     * docs/decisions/0003-weekly-reporting-period.md) containing the given date.
     */
    public static function weekContaining(CarbonInterface $date): self
    {
        $date = $date->copy()->startOfDay();
        $daysSinceThursday = ($date->dayOfWeek - CarbonInterface::THURSDAY + 7) % 7;
        $start = $date->copy()->subDays($daysSinceThursday);

        return new self($start, $start->copy()->addDays(6));
    }

    /**
     * Number of calendar days in the period, inclusive of both ends.
     */
    public function days(): int
    {
        return (int) $this->start->copy()->startOfDay()->diffInDays($this->end->copy()->startOfDay()) + 1;
    }

    public function contains(CarbonInterface $date): bool
    {
        return ! $date->lt($this->start->copy()->startOfDay()) && ! $date->gt($this->end->copy()->endOfDay());
    }

    /**
     * Number of days this period overlaps the given range. `$to` may be null
     * for a range that is still ongoing (defaults to the period's own end).
     */
    public function overlapDays(CarbonInterface $from, ?CarbonInterface $to): int
    {
        $rangeStart = $from->copy()->startOfDay();
        $rangeEnd = ($to ?? $this->end)->copy()->startOfDay();

        $overlapStart = $rangeStart->greaterThan($this->start) ? $rangeStart : $this->start->copy()->startOfDay();
        $overlapEnd = $rangeEnd->lessThan($this->end) ? $rangeEnd : $this->end->copy()->startOfDay();

        if ($overlapStart->gt($overlapEnd)) {
            return 0;
        }

        return (int) $overlapStart->diffInDays($overlapEnd) + 1;
    }
}
