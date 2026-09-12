<?php

namespace Tests\Feature\Services\Financial;

use App\Enums\TireEventType;
use App\Models\Tire;
use App\Models\TireEvent;
use App\Models\Truck;
use App\Services\Financial\Period;
use App\Services\Financial\TireCostEstimator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TireCostEstimatorTest extends TestCase
{
    use RefreshDatabase;

    private function period(): Period
    {
        return new Period(Carbon::parse('2026-09-10'), Carbon::parse('2026-09-16'));
    }

    public function test_amortises_a_still_mounted_tire_over_its_elapsed_weeks(): void
    {
        $this->travelTo(Carbon::parse('2026-09-16 12:00:00'));

        $truck = Truck::factory()->create();
        $tire = Tire::factory()->for($truck, 'currentTruck')->create(['purchase_cost' => 70000]);

        TireEvent::factory()->for($tire)->for($truck)->create([
            'event_type' => TireEventType::Mount,
            'occurred_at' => '2026-08-12 12:00:00', // exactly 5 weeks before "now" (same time of day)
        ]);

        $cost = app(TireCostEstimator::class)->costFor($truck, $this->period());

        // 70000 / 5 weeks mounted = 14000/week, and the whole period overlaps.
        $this->assertSame(14000.0, $cost);
    }

    public function test_excludes_a_tire_dismounted_before_the_period(): void
    {
        $truck = Truck::factory()->create();
        $tire = Tire::factory()->for($truck, 'currentTruck')->create(['purchase_cost' => 70000]);

        TireEvent::factory()->for($tire)->for($truck)->create([
            'event_type' => TireEventType::Mount,
            'occurred_at' => '2026-07-01 08:00:00',
        ]);
        TireEvent::factory()->for($tire)->for($truck)->create([
            'event_type' => TireEventType::Dismount,
            'occurred_at' => '2026-08-01 08:00:00',
        ]);

        $cost = app(TireCostEstimator::class)->costFor($truck, $this->period());

        $this->assertSame(0.0, $cost);
    }

    public function test_contributes_nothing_for_a_tire_with_no_mount_event(): void
    {
        $truck = Truck::factory()->create();
        Tire::factory()->for($truck, 'currentTruck')->create(['purchase_cost' => 70000]);

        $cost = app(TireCostEstimator::class)->costFor($truck, $this->period());

        $this->assertSame(0.0, $cost);
    }
}
