<?php

namespace Tests\Unit\Services\Financial;

use App\Enums\BillingCycle;
use App\Services\Financial\Period;
use App\Services\Financial\ProrationCalculator;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class ProrationCalculatorTest extends TestCase
{
    private function sevenDayPeriod(): Period
    {
        return new Period(Carbon::parse('2026-09-10'), Carbon::parse('2026-09-16'));
    }

    public function test_prorates_a_monthly_amount_to_a_seven_day_period(): void
    {
        $calculator = new ProrationCalculator;

        $result = $calculator->toPeriod(30440.0, BillingCycle::Monthly, $this->sevenDayPeriod());

        // 30440 * 7 / 30.44 = 7000.00
        $this->assertSame(7000.0, $result);
    }

    public function test_prorates_a_quarterly_amount_to_a_seven_day_period(): void
    {
        $calculator = new ProrationCalculator;

        $result = $calculator->toPeriod(91310.0, BillingCycle::Quarterly, $this->sevenDayPeriod());

        // 91310 * 7 / 91.31 = 7000.00
        $this->assertSame(7000.0, $result);
    }

    public function test_prorates_an_annual_amount_to_a_seven_day_period(): void
    {
        $calculator = new ProrationCalculator;

        $result = $calculator->toPeriod(365000.0, BillingCycle::Annual, $this->sevenDayPeriod());

        // 365000 * 7 / 365 = 7000.00
        $this->assertSame(7000.0, $result);
    }
}
