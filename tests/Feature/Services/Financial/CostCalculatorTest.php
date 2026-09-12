<?php

namespace Tests\Feature\Services\Financial;

use App\Enums\BillingCycle;
use App\Enums\MaintenanceType;
use App\Models\CostType;
use App\Models\FuelRecord;
use App\Models\FuelStation;
use App\Models\Maintenance;
use App\Models\MaintenanceProvider;
use App\Models\TollRecord;
use App\Models\Truck;
use App\Models\TruckExpense;
use App\Models\TruckFixedCost;
use App\Services\Financial\CostCalculator;
use App\Services\Financial\Period;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CostCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private function period(): Period
    {
        return new Period(Carbon::parse('2026-09-10'), Carbon::parse('2026-09-16'));
    }

    public function test_sums_direct_costs_from_fuel_tolls_maintenance_and_truck_expenses(): void
    {
        $truck = Truck::factory()->create();
        $station = FuelStation::factory()->create();
        $provider = MaintenanceProvider::factory()->create();
        $costType = CostType::factory()->create();

        FuelRecord::factory()->for($truck)->for($station, 'fuelStation')->create([
            'occurred_at' => '2026-09-11 08:00:00',
            'total' => 50000,
        ]);

        TollRecord::factory()->for($truck)->create([
            'occurred_at' => '2026-09-11 09:00:00',
            'amount' => 3000,
        ]);

        Maintenance::factory()->for($truck)->for($provider, 'provider')->create([
            'type' => MaintenanceType::Preventive,
            'completion_date' => '2026-09-12',
            'total' => 15000,
        ]);

        TruckExpense::factory()->for($truck)->for($costType, 'costType')->create([
            'expense_date' => '2026-09-13',
            'amount' => 2000,
        ]);

        $costs = app(CostCalculator::class)->forTruck($truck, $this->period());

        $this->assertSame(50000.0, $costs['fuel']);
        $this->assertSame(3000.0, $costs['tolls']);
        $this->assertSame(15000.0, $costs['maintenance']);
        $this->assertSame(0.0, $costs['tires']);
        $this->assertSame(2000.0, $costs['truck_expenses']);
        $this->assertSame(70000.0, $costs['direct_cost']);
    }

    public function test_excludes_records_outside_the_period(): void
    {
        $truck = Truck::factory()->create();
        $station = FuelStation::factory()->create();

        FuelRecord::factory()->for($truck)->for($station, 'fuelStation')->create([
            'occurred_at' => '2026-09-01 08:00:00', // a week before the period
            'total' => 99999,
        ]);

        $costs = app(CostCalculator::class)->forTruck($truck, $this->period());

        $this->assertSame(0.0, $costs['fuel']);
        $this->assertSame(0.0, $costs['direct_cost']);
    }

    public function test_prorates_a_monthly_truck_fixed_cost_to_the_period(): void
    {
        $truck = Truck::factory()->create();
        $costType = CostType::factory()->create();

        TruckFixedCost::factory()->for($truck)->for($costType, 'costType')->create([
            'amount' => 30440,
            'billing_cycle' => BillingCycle::Monthly,
            'effective_from' => '2026-01-01',
            'effective_to' => null,
        ]);

        $costs = app(CostCalculator::class)->forTruck($truck, $this->period());

        $this->assertSame(7000.0, $costs['truck_fixed_costs']);
        $this->assertSame(7000.0, $costs['indirect_cost']);
    }

    public function test_excludes_a_truck_fixed_cost_that_ended_before_the_period(): void
    {
        $truck = Truck::factory()->create();
        $costType = CostType::factory()->create();

        TruckFixedCost::factory()->for($truck)->for($costType, 'costType')->create([
            'amount' => 30440,
            'billing_cycle' => BillingCycle::Monthly,
            'effective_from' => '2026-01-01',
            'effective_to' => '2026-08-01',
        ]);

        $costs = app(CostCalculator::class)->forTruck($truck, $this->period());

        $this->assertSame(0.0, $costs['truck_fixed_costs']);
    }
}
