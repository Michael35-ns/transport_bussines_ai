<?php

namespace Tests\Feature\Livewire\Drivers;

use App\Enums\ActiveStatus;
use App\Livewire\Drivers\Index;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('drivers.index'))->assertRedirect(route('login'));
    }

    public function test_a_viewer_can_see_the_driver_list(): void
    {
        Driver::factory()->create(['name' => 'Carlos Vargas']);

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)->assertSee('Carlos Vargas');
    }

    public function test_the_search_field_filters_by_name(): void
    {
        Driver::factory()->create(['name' => 'Carlos Vargas']);
        Driver::factory()->create(['name' => 'Willy Mora']);

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->set('search', 'Willy')
            ->assertSee('Willy Mora')
            ->assertDontSee('Carlos Vargas');
    }

    public function test_an_admin_can_create_a_driver(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('name', 'Carlos Vargas')
            ->set('hourly_rate', 1200)
            ->set('status', ActiveStatus::Active->value)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('drivers', ['name' => 'Carlos Vargas']);
    }

    public function test_creating_a_driver_requires_an_hourly_rate(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('name', 'Carlos Vargas')
            ->set('hourly_rate', null)
            ->call('save')
            ->assertHasErrors(['hourly_rate' => 'required']);
    }

    public function test_a_viewer_cannot_create_a_driver(): void
    {
        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->set('name', 'Carlos Vargas')
            ->set('hourly_rate', 1200)
            ->set('status', ActiveStatus::Active->value)
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('drivers', ['name' => 'Carlos Vargas']);
    }

    public function test_an_admin_can_edit_a_driver(): void
    {
        $driver = Driver::factory()->create(['name' => 'Old Name']);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('edit', $driver)
            ->assertSet('name', 'Old Name')
            ->set('name', 'New Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('New Name', $driver->fresh()->name);
    }

    public function test_an_admin_can_delete_a_driver(): void
    {
        $driver = Driver::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)->call('delete', $driver);

        $this->assertSoftDeleted($driver);
    }

    public function test_a_viewer_cannot_delete_a_driver(): void
    {
        $driver = Driver::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->call('delete', $driver)
            ->assertForbidden();

        $this->assertModelExists($driver);
    }
}
