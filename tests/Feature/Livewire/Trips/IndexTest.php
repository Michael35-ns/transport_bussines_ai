<?php

namespace Tests\Feature\Livewire\Trips;

use App\Enums\TripStatus;
use App\Livewire\Trips\Index;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\RateAgreement;
use App\Models\Route;
use App\Models\Trip;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('trips.index'))->assertRedirect(route('login'));
    }

    public function test_a_viewer_can_see_the_trip_list(): void
    {
        $truck = Truck::factory()->create(['plate' => 'SJB-123']);
        Trip::factory()->create(['truck_id' => $truck->id]);

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)->assertSee('SJB-123');
    }

    public function test_the_search_field_filters_by_truck_plate(): void
    {
        // The create/edit modal always lists every truck/driver/route as
        // dropdown options, so assertSee/assertDontSee on those identifiers
        // can't tell the filtered table apart from the ever-present modal
        // markup — assert on the filtered paginator itself instead.
        $matching = Truck::factory()->create(['plate' => 'SJB-123']);
        $other = Truck::factory()->create(['plate' => 'CRC-999']);
        $matchingTrip = Trip::factory()->create(['truck_id' => $matching->id]);
        Trip::factory()->create(['truck_id' => $other->id]);

        $this->actingAs(User::factory()->viewer()->create());

        $paginator = Livewire::test(Index::class)
            ->set('search', 'SJB')
            ->instance()
            ->trips();

        $this->assertSame(1, $paginator->total());
        $this->assertSame($matchingTrip->id, $paginator->first()->id);
    }

    public function test_selecting_a_route_defaults_the_distance_to_its_standard_km(): void
    {
        $route = Route::factory()->create(['standard_km' => 42.5]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('route_id', (string) $route->id)
            ->assertSet('distance', 42.5)
            ->assertSet('distance_estimated', true);
    }

    public function test_editing_the_distance_manually_marks_it_as_not_estimated(): void
    {
        $route = Route::factory()->create(['standard_km' => 42.5]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('route_id', (string) $route->id)
            ->set('distance', 50.0)
            ->assertSet('distance_estimated', false);
    }

    public function test_selecting_a_rate_agreement_defaults_the_price(): void
    {
        $route = Route::factory()->create();
        $agreement = RateAgreement::factory()->create(['route_id' => $route->id, 'price' => 75000]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('route_id', (string) $route->id)
            ->set('rate_agreement_id', (string) $agreement->id)
            ->assertSet('price', 75000.0);
    }

    public function test_an_admin_can_create_a_trip_with_a_rate_agreement(): void
    {
        $truck = Truck::factory()->create();
        $driver = Driver::factory()->create();
        $route = Route::factory()->create(['standard_km' => 30]);
        $customer = Customer::factory()->create();
        $agreement = RateAgreement::factory()->create([
            'customer_id' => $customer->id,
            'route_id' => $route->id,
            'price' => 60000,
        ]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('truck_id', (string) $truck->id)
            ->set('driver_id', (string) $driver->id)
            ->set('route_id', (string) $route->id)
            ->set('rate_agreement_id', (string) $agreement->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('trips', [
            'truck_id' => $truck->id,
            'rate_agreement_id' => $agreement->id,
            'price' => '60000.00',
            'distance' => '30.00',
        ]);
    }

    public function test_a_new_trip_is_automatically_completed_with_todays_date(): void
    {
        $truck = Truck::factory()->create();
        $driver = Driver::factory()->create();
        $route = Route::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('truck_id', (string) $truck->id)
            ->set('driver_id', (string) $driver->id)
            ->set('route_id', (string) $route->id)
            ->set('price', 10000)
            ->call('save')
            ->assertHasNoErrors();

        $trip = Trip::where('truck_id', $truck->id)->firstOrFail();

        $this->assertSame(TripStatus::Completed, $trip->status);
        $this->assertTrue($trip->actual_end->isToday());
    }

    public function test_a_viewer_cannot_create_a_trip(): void
    {
        $truck = Truck::factory()->create();
        $driver = Driver::factory()->create();
        $route = Route::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->set('truck_id', (string) $truck->id)
            ->set('driver_id', (string) $driver->id)
            ->set('route_id', (string) $route->id)
            ->set('price', 10000)
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('trips', ['truck_id' => $truck->id]);
    }

    public function test_an_admin_can_edit_a_trip_without_changing_its_completion_date(): void
    {
        $trip = Trip::factory()->create(['actual_end' => now()->subWeek(), 'price' => 40000]);
        $originalActualEnd = $trip->actual_end;

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('edit', $trip)
            ->assertSet('price', 40000.0)
            ->set('price', 55000)
            ->call('save')
            ->assertHasNoErrors();

        $trip->refresh();
        $this->assertSame('55000.00', $trip->price);
        $this->assertTrue($originalActualEnd->equalTo($trip->actual_end));
    }

    public function test_an_admin_can_delete_a_trip(): void
    {
        $trip = Trip::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)->call('delete', $trip);

        $this->assertModelMissing($trip);
    }

    public function test_a_viewer_cannot_delete_a_trip(): void
    {
        $trip = Trip::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->call('delete', $trip)
            ->assertForbidden();

        $this->assertModelExists($trip);
    }
}
