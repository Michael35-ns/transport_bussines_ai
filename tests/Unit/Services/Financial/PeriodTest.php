<?php

namespace Tests\Unit\Services\Financial;

use App\Services\Financial\Period;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class PeriodTest extends TestCase
{
    public function test_week_containing_a_thursday_starts_on_that_thursday(): void
    {
        $period = Period::weekContaining(Carbon::parse('2026-09-10')); // a Thursday

        $this->assertSame('2026-09-10', $period->start->toDateString());
        $this->assertSame('2026-09-16', $period->end->toDateString());
    }

    public function test_week_containing_a_midweek_date_resolves_to_the_thursday_wednesday_bounds(): void
    {
        $period = Period::weekContaining(Carbon::parse('2026-09-14')); // the following Monday

        $this->assertSame('2026-09-10', $period->start->toDateString());
        $this->assertSame('2026-09-16', $period->end->toDateString());
    }

    public function test_week_containing_a_wednesday_ends_on_that_wednesday(): void
    {
        $period = Period::weekContaining(Carbon::parse('2026-09-16')); // a Wednesday

        $this->assertSame('2026-09-10', $period->start->toDateString());
        $this->assertSame('2026-09-16', $period->end->toDateString());
    }

    public function test_days_counts_seven_for_a_full_week(): void
    {
        $period = new Period(Carbon::parse('2026-09-10'), Carbon::parse('2026-09-16'));

        $this->assertSame(7, $period->days());
    }

    public function test_contains_is_true_for_a_date_inside_the_period(): void
    {
        $period = new Period(Carbon::parse('2026-09-10'), Carbon::parse('2026-09-16'));

        $this->assertTrue($period->contains(Carbon::parse('2026-09-13')));
        $this->assertFalse($period->contains(Carbon::parse('2026-09-17')));
    }

    public function test_overlap_days_returns_the_full_range_when_fully_contained(): void
    {
        $period = new Period(Carbon::parse('2026-09-10'), Carbon::parse('2026-09-16'));

        $this->assertSame(7, $period->overlapDays(Carbon::parse('2026-09-10'), Carbon::parse('2026-09-16')));
    }

    public function test_overlap_days_returns_zero_when_the_range_is_entirely_before_the_period(): void
    {
        $period = new Period(Carbon::parse('2026-09-10'), Carbon::parse('2026-09-16'));

        $this->assertSame(0, $period->overlapDays(Carbon::parse('2026-08-01'), Carbon::parse('2026-08-10')));
    }

    public function test_overlap_days_uses_the_period_end_for_an_open_ended_range(): void
    {
        $period = new Period(Carbon::parse('2026-09-10'), Carbon::parse('2026-09-16'));

        // Mounted from the middle of the period onward, with no end date yet.
        $this->assertSame(4, $period->overlapDays(Carbon::parse('2026-09-13'), null));
    }
}
