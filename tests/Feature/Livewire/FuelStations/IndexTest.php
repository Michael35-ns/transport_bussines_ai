<?php

namespace Tests\Feature\Livewire\FuelStations;

use App\Livewire\FuelStations\Index;
use App\Models\FuelStation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('fuel-stations.index'))->assertRedirect(route('login'));
    }

    public function test_a_viewer_can_see_the_station_list(): void
    {
        FuelStation::factory()->create(['name' => 'Servicentro Los Santos']);

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)->assertSee('Servicentro Los Santos');
    }

    public function test_an_admin_can_create_a_station(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('name', 'Servicentro Los Santos')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('fuel_stations', ['name' => 'Servicentro Los Santos']);
    }

    public function test_creating_a_station_requires_a_name(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_a_viewer_cannot_create_a_station(): void
    {
        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->set('name', 'Servicentro Los Santos')
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('fuel_stations', ['name' => 'Servicentro Los Santos']);
    }

    public function test_an_admin_can_edit_a_station(): void
    {
        $station = FuelStation::factory()->create(['name' => 'Old Name']);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('edit', $station)
            ->assertSet('name', 'Old Name')
            ->set('name', 'New Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('New Name', $station->fresh()->name);
    }

    public function test_an_admin_can_delete_a_station(): void
    {
        $station = FuelStation::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)->call('delete', $station);

        $this->assertSoftDeleted($station);
    }

    public function test_a_viewer_cannot_delete_a_station(): void
    {
        $station = FuelStation::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->call('delete', $station)
            ->assertForbidden();

        $this->assertModelExists($station);
    }
}
