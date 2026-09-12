<?php

namespace Tests\Feature\Services\Financial;

use App\Enums\AllocationMethod;
use App\Enums\BillingCycle;
use App\Enums\TripStatus;
use App\Models\CostType;
use App\Models\Driver;
use App\Models\DriverWorklog;
use App\Models\OverheadCost;
use App\Models\Route;
use App\Models\Trip;
use App\Models\Truck;
use App\Services\Financial\OverheadAllocationService;
use App\Services\Financial\Period;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OverheadAllocationServiceTest extends TestCase
{
    use RefreshDatabase;

    private function period(): Period
    {
        return new Period(Carbon::parse('2026-09-10'), Carbon::parse('2026-09-16'));
    }

    public function test_splits_the_overhead_pool_by_each_trucks_share_of_worked_days(): void
    {
        $truckA = Truck::factory()->create();
        $truckB = Truck::factory()->create();
        $route = Route::factory()->create();
        $driver = Driver::factory()->create();

        $costType = CostType::factory()->create();
        OverheadCost::factory()->for($costType, 'costType')->create([
            'amount' => 70000,
            'billing_cycle' => BillingCycle::Monthly,
            'effective_from' => '2026-01-01',
            'effective_to' => null,
        ]);

        // Truck A worked 2 days, truck B worked 1 day within the period.
        Trip::factory()->for($truckA)->for($driver)->for($route)->create([
            'status' => TripStatus::Completed,
            'actual_start' => '2026-09-11 08:00:00',
            'actual_end' => '2026-09-11 12:00:00',
        ]);
        Trip::factory()->for($truckA)->for($driver)->for($route)->create([
            'status' => TripStatus::Completed,
            'actual_start' => '2026-09-12 08:00:00',
            'actual_end' => '2026-09-12 12:00:00',
        ]);
        Trip::factory()->for($truckB)->for($driver)->for($route)->create([
            'status' => TripStatus::Completed,
            'actual_start' => '2026-09-13 08:00:00',
            'actual_end' => '2026-09-13 12:00:00',
        ]);

        $allocations = app(OverheadAllocationService::class)->allocate($this->period());

        $forTruckA = $allocations->firstWhere('truck_id', $truckA->id);
        $forTruckB = $allocations->firstWhere('truck_id', $truckB->id);

        $this->assertSame(AllocationMethod::WorkedDays, $forTruckA->method);
        $this->assertEqualsWithDelta(2 / 3, (float) $forTruckA->weight, 0.000001);
        $this->assertEqualsWithDelta(1 / 3, (float) $forTruckB->weight, 0.000001);

        $pool = round(70000 * 7 / 30.44, 2);
        $this->assertEqualsWithDelta(round($pool * 2 / 3, 2), (float) $forTruckA->amount, 0.01);
        $this->assertEqualsWithDelta(round($pool * 1 / 3, 2), (float) $forTruckB->amount, 0.01);
    }

    public function test_allocates_nothing_when_no_truck_has_worked_days(): void
    {
        $truck = Truck::factory()->create();

        $allocations = app(OverheadAllocationService::class)->allocate($this->period());
        $allocation = $allocations->firstWhere('truck_id', $truck->id);

        $this->assertSame(0.0, (float) $allocation->weight);
        $this->assertSame(0.0, (float) $allocation->amount);
    }

    public function test_includes_driver_worklog_pay_in_the_overhead_pool(): void
    {
        $truck = Truck::factory()->create();
        $driver = Driver::factory()->create();
        $route = Route::factory()->create();

        DriverWorklog::factory()->for($driver)->create([
            'work_date' => '2026-09-11',
            'hours' => 8,
            'hourly_rate_snapshot' => 1000,
            'computed_pay' => 8000,
        ]);

        Trip::factory()->for($truck)->for($driver)->for($route)->create([
            'status' => TripStatus::Completed,
            'actual_start' => '2026-09-11 08:00:00',
            'actual_end' => '2026-09-11 16:00:00',
        ]);

        $allocations = app(OverheadAllocationService::class)->allocate($this->period());
        $allocation = $allocations->firstWhere('truck_id', $truck->id);

        // The only truck with worked days gets 100% of the pool, which here
        // is entirely the driver's pay (no overhead_costs rows exist).
        $this->assertSame(8000.0, (float) $allocation->amount);
    }
}
