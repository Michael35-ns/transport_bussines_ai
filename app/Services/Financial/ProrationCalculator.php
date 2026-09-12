<?php

namespace App\Services\Financial;

use App\Enums\BillingCycle;

/**
 * Prorates a periodic (monthly/quarterly/annual) billed amount to a given
 * period, per docs/finance/financial-model.md §J.3. The source row keeps the
 * real billing amount and cycle — this is a derived view only.
 */
class ProrationCalculator
{
    /**
     * @var array<string, float>
     */
    private const DAYS_IN_CYCLE = [
        'monthly' => 30.44,
        'quarterly' => 91.31,
        'annual' => 365.0,
    ];

    public function toPeriod(float $amount, BillingCycle $cycle, Period $period): float
    {
        $daysInCycle = self::DAYS_IN_CYCLE[$cycle->value];

        return round($amount * $period->days() / $daysInCycle, 2);
    }
}
