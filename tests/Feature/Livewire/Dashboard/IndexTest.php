<?php

namespace Tests\Feature\Livewire\Dashboard;

use App\Enums\MaintenanceIntervalType;
use App\Enums\TripStatus;
use App\Livewire\Dashboard\Index;
use App\Models\MaintenanceSchedule;
use App\Models\Route;
use App\Models\Trip;
use App\Models\Truck;
use App\Models\User;
use App\Services\Financial\Period;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_a_viewer_can_see_the_dashboard(): void
    {
        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)->assertOk();
    }

    public function test_fleet_totals_reflect_completed_trips_in_the_current_week(): void
    {
        $period = Period::weekContaining(now());
        $route = Route::factory()->create(['standard_km' => 100]);
        $truck = Truck::factory()->create();
        Trip::factory()->create([
            'truck_id' => $truck->id,
            'route_id' => $route->id,
            'status' => TripStatus::Completed,
            'actual_end' => $period->start->copy()->addDay(),
            'distance' => 100,
            'price' => 60000,
        ]);

        $this->actingAs(User::factory()->viewer()->create());

        $totals = Livewire::test(Index::class)->instance()->fleetTotals;

        $this->assertSame(60000.0, $totals['revenue']);
        $this->assertSame(100.0, $totals['km']);
    }

    public function test_a_trip_outside_the_selected_week_is_excluded(): void
    {
        $period = Period::weekContaining(now());
        $truck = Truck::factory()->create();
        Trip::factory()->create([
            'truck_id' => $truck->id,
            'status' => TripStatus::Completed,
            'actual_end' => $period->start->copy()->subWeek(),
            'price' => 60000,
        ]);

        $this->actingAs(User::factory()->viewer()->create());

        $totals = Livewire::test(Index::class)->instance()->fleetTotals;

        $this->assertSame(0.0, $totals['revenue']);
    }

    public function test_previous_and_next_week_shift_the_period_by_seven_days(): void
    {
        $this->actingAs(User::factory()->viewer()->create());

        $component = Livewire::test(Index::class);
        $originalStart = $component->instance()->period()->start->toDateString();

        $component->call('previousWeek');
        $previousStart = $component->instance()->period()->start->toDateString();

        $component->call('nextWeek')->call('nextWeek');
        $nextStart = $component->instance()->period()->start->toDateString();

        $this->assertTrue(Carbon::parse($previousStart)->addWeek()->isSameDay(Carbon::parse($originalStart)));
        $this->assertTrue(Carbon::parse($nextStart)->subWeek()->isSameDay(Carbon::parse($originalStart)));
    }

    public function test_maintenance_alerts_count_overdue_and_upcoming_schedules(): void
    {
        $overdueTruck = Truck::factory()->create(['current_odometer' => 510000]);
        MaintenanceSchedule::factory()->create([
            'truck_id' => $overdueTruck->id,
            'interval_type' => MaintenanceIntervalType::Km,
            'interval_value' => 5000,
            'last_done_odometer' => 500000,
            'lead_km' => 500,
            'active' => true,
        ]);

        $upcomingTruck = Truck::factory()->create(['current_odometer' => 504800]);
        MaintenanceSchedule::factory()->create([
            'truck_id' => $upcomingTruck->id,
            'interval_type' => MaintenanceIntervalType::Km,
            'interval_value' => 5000,
            'last_done_odometer' => 500000,
            'lead_km' => 500,
            'active' => true,
        ]);

        $okTruck = Truck::factory()->create(['current_odometer' => 501000]);
        MaintenanceSchedule::factory()->create([
            'truck_id' => $okTruck->id,
            'interval_type' => MaintenanceIntervalType::Km,
            'interval_value' => 5000,
            'last_done_odometer' => 500000,
            'lead_km' => 500,
            'active' => true,
        ]);

        $this->actingAs(User::factory()->viewer()->create());

        $alerts = Livewire::test(Index::class)->instance()->maintenanceAlerts;

        $this->assertSame(1, $alerts['overdue']);
        $this->assertSame(1, $alerts['upcoming']);
    }
}
