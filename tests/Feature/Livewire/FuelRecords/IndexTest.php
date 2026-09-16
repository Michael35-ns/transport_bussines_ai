<?php

namespace Tests\Feature\Livewire\FuelRecords;

use App\Livewire\FuelRecords\Index;
use App\Models\FuelRecord;
use App\Models\FuelStation;
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
        $this->get(route('fuel-records.index'))->assertRedirect(route('login'));
    }

    public function test_a_viewer_can_see_the_record_list(): void
    {
        $truck = Truck::factory()->create(['plate' => 'SJB-123']);
        FuelRecord::factory()->create(['truck_id' => $truck->id]);

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)->assertSee('SJB-123');
    }

    public function test_the_total_is_computed_live_from_liters_and_unit_price(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('liters', 80)
            ->set('unit_price', 750)
            ->assertSet('totalPreview', 60000.0);
    }

    public function test_an_admin_can_create_a_fuel_record(): void
    {
        $truck = Truck::factory()->create();
        $station = FuelStation::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('truck_id', (string) $truck->id)
            ->set('fuel_station_id', (string) $station->id)
            ->set('liters', 80)
            ->set('unit_price', 750)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('fuel_records', [
            'truck_id' => $truck->id,
            'fuel_station_id' => $station->id,
            'total' => '60000.00',
        ]);
    }

    public function test_creating_a_fuel_record_requires_a_station(): void
    {
        $truck = Truck::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('truck_id', (string) $truck->id)
            ->set('fuel_station_id', '')
            ->set('liters', 80)
            ->set('unit_price', 750)
            ->call('save')
            ->assertHasErrors(['fuel_station_id']);
    }

    public function test_a_viewer_cannot_create_a_fuel_record(): void
    {
        $truck = Truck::factory()->create();
        $station = FuelStation::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->set('truck_id', (string) $truck->id)
            ->set('fuel_station_id', (string) $station->id)
            ->set('liters', 80)
            ->set('unit_price', 750)
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('fuel_records', ['truck_id' => $truck->id]);
    }

    public function test_an_admin_can_edit_a_fuel_record(): void
    {
        $record = FuelRecord::factory()->create(['liters' => 50]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('edit', $record)
            ->assertSet('liters', 50.0)
            ->set('liters', 60)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('60.0000', $record->fresh()->liters);
    }

    public function test_an_admin_can_delete_a_fuel_record(): void
    {
        $record = FuelRecord::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)->call('delete', $record);

        $this->assertModelMissing($record);
    }

    public function test_a_viewer_cannot_delete_a_fuel_record(): void
    {
        $record = FuelRecord::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->call('delete', $record)
            ->assertForbidden();

        $this->assertModelExists($record);
    }
}
