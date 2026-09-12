<?php

namespace Tests\Feature\Services\Financial;

use App\Enums\MaintenanceType;
use App\Enums\TripStatus;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\FuelRecord;
use App\Models\FuelStation;
use App\Models\Maintenance;
use App\Models\MaintenanceProvider;
use App\Models\RateAgreement;
use App\Models\Route;
use App\Models\Trip;
use App\Models\Truck;
use App\Services\Financial\FinancialCalculator;
use App\Services\Financial\Period;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FinancialCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private function period(): Period
    {
        return new Period(Carbon::parse('2026-09-10'), Carbon::parse('2026-09-16'));
    }

    public function test_truck_summary_shows_zero_and_null_metrics_when_the_truck_has_no_trips(): void
    {
        $truck = Truck::factory()->create();

        $summary = app(FinancialCalculator::class)->truckSummary($truck, $this->period());

        $this->assertSame(0.0, $summary['revenue']);
        $this->assertSame(0.0, $summary['km']);
        $this->assertNull($summary['margin_pct']);
        $this->assertNull($summary['cost_per_km']);
        $this->assertNull($summary['revenue_per_km']);
        $this->assertNull($summary['profit_per_km']);
        $this->assertSame(100.0, $summary['availability_pct']);
        $this->assertSame(0.0, $summary['utilization_pct']);
    }

    public function test_truck_summary_computes_revenue_cost_profit_margin_and_per_km_metrics(): void
    {
        $route = Route::factory()->create(['standard_km' => 100]);
        $truck = Truck::factory()->create();
        $driver = Driver::factory()->create();
        $station = FuelStation::factory()->create();

        Trip::factory()->for($truck)->for($driver)->for($route)->create([
            'distance' => 100,
            'price' => 50000,
            'status' => TripStatus::Completed,
            'actual_start' => '2026-09-11 06:00:00',
            'actual_end' => '2026-09-11 14:00:00',
        ]);

        FuelRecord::factory()->for($truck)->for($station, 'fuelStation')->create([
            'occurred_at' => '2026-09-11 07:00:00',
            'total' => 20000,
        ]);

        $summary = app(FinancialCalculator::class)->truckSummary($truck, $this->period());

        $this->assertSame(50000.0, $summary['revenue']);
        $this->assertSame(100.0, $summary['km']);
        $this->assertSame(20000.0, $summary['direct_cost']);
        $this->assertSame(0.0, $summary['indirect_cost']);
        $this->assertSame(20000.0, $summary['total_cost']);
        $this->assertSame(30000.0, $summary['profit_contribution']);
        $this->assertSame(30000.0, $summary['profit_net']);
        $this->assertSame(60.0, $summary['margin_pct']);
        $this->assertSame(200.0, $summary['cost_per_km']);
        $this->assertSame(500.0, $summary['revenue_per_km']);
        $this->assertSame(300.0, $summary['profit_per_km']);
    }

    public function test_availability_counts_only_corrective_downtime_not_preventive(): void
    {
        $truck = Truck::factory()->create();
        $provider = MaintenanceProvider::factory()->create();

        Maintenance::factory()->for($truck)->for($provider, 'provider')->create([
            'type' => MaintenanceType::Preventive,
            'completion_date' => '2026-09-12',
            'downtime_days' => 2,
        ]);
        Maintenance::factory()->for($truck)->for($provider, 'provider')->create([
            'type' => MaintenanceType::Corrective,
            'completion_date' => '2026-09-13',
            'downtime_days' => 1,
        ]);

        $availability = app(FinancialCalculator::class)->availability($truck, $this->period());

        // 7 days − 1 corrective downtime day = 6 available days out of 7.
        $this->assertEqualsWithDelta(6 / 7, $availability, 0.0001);
    }

    public function test_utilization_is_worked_days_over_available_days(): void
    {
        $truck = Truck::factory()->create();
        $driver = Driver::factory()->create();
        $route = Route::factory()->create();

        Trip::factory()->for($truck)->for($driver)->for($route)->create([
            'status' => TripStatus::Completed,
            'actual_start' => '2026-09-11 08:00:00',
            'actual_end' => '2026-09-11 12:00:00',
        ]);
        Trip::factory()->for($truck)->for($driver)->for($route)->create([
            'status' => TripStatus::Completed,
            'actual_start' => '2026-09-12 08:00:00',
            'actual_end' => '2026-09-12 12:00:00',
        ]);

        $utilization = app(FinancialCalculator::class)->utilization($truck, $this->period());

        // 2 worked days / 7 available days (no downtime this period).
        $this->assertEqualsWithDelta(2 / 7, $utilization, 0.0001);
    }

    public function test_route_profitability_sums_only_trips_on_that_route(): void
    {
        $route = Route::factory()->create();
        $otherRoute = Route::factory()->create();
        $truck = Truck::factory()->create();
        $driver = Driver::factory()->create();

        Trip::factory()->for($truck)->for($driver)->for($route)->create([
            'status' => TripStatus::Completed,
            'actual_end' => '2026-09-11 12:00:00',
            'price' => 40000,
            'distance' => 80,
        ]);
        Trip::factory()->for($truck)->for($driver)->for($otherRoute)->create([
            'status' => TripStatus::Completed,
            'actual_end' => '2026-09-12 12:00:00',
            'price' => 99999,
            'distance' => 50,
        ]);

        $result = app(FinancialCalculator::class)->routeProfitability($route, $this->period());

        $this->assertSame(1, $result['trips']);
        $this->assertSame(40000.0, $result['revenue']);
    }

    public function test_customer_profitability_attributes_trips_via_their_rate_agreement(): void
    {
        $customer = Customer::factory()->create();
        $otherCustomer = Customer::factory()->create();
        $route = Route::factory()->create();
        $truck = Truck::factory()->create();
        $driver = Driver::factory()->create();

        $agreement = RateAgreement::factory()->for($customer)->for($route)->create();
        $otherAgreement = RateAgreement::factory()->for($otherCustomer)->for($route)->create();

        Trip::factory()->for($truck)->for($driver)->for($route)->create([
            'rate_agreement_id' => $agreement->id,
            'status' => TripStatus::Completed,
            'actual_end' => '2026-09-11 12:00:00',
            'price' => 55000,
        ]);
        Trip::factory()->for($truck)->for($driver)->for($route)->create([
            'rate_agreement_id' => $otherAgreement->id,
            'status' => TripStatus::Completed,
            'actual_end' => '2026-09-12 12:00:00',
            'price' => 12345,
        ]);

        $result = app(FinancialCalculator::class)->customerProfitability($customer, $this->period());

        $this->assertSame(1, $result['trips']);
        $this->assertSame(55000.0, $result['revenue']);
    }

    public function test_customer_profitability_excludes_trips_with_no_rate_agreement_or_invoice(): void
    {
        $customer = Customer::factory()->create();
        $route = Route::factory()->create();
        $truck = Truck::factory()->create();
        $driver = Driver::factory()->create();

        Trip::factory()->for($truck)->for($driver)->for($route)->create([
            'rate_agreement_id' => null,
            'status' => TripStatus::Completed,
            'actual_end' => '2026-09-11 12:00:00',
            'price' => 999999,
        ]);

        $result = app(FinancialCalculator::class)->customerProfitability($customer, $this->period());

        $this->assertSame(0, $result['trips']);
        $this->assertSame(0.0, $result['revenue']);
    }
}
