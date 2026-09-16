<?php

namespace Tests\Feature\Livewire\MaintenanceProviders;

use App\Livewire\MaintenanceProviders\Index;
use App\Models\MaintenanceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('maintenance-providers.index'))->assertRedirect(route('login'));
    }

    public function test_a_viewer_can_see_the_provider_list(): void
    {
        MaintenanceProvider::factory()->create(['name' => 'Taller Los Santos']);

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)->assertSee('Taller Los Santos');
    }

    public function test_an_admin_can_create_a_provider(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('name', 'Taller Los Santos')
            ->set('specialty', 'general')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('maintenance_providers', ['name' => 'Taller Los Santos']);
    }

    public function test_creating_a_provider_requires_a_name(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_a_viewer_cannot_create_a_provider(): void
    {
        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->set('name', 'Taller Los Santos')
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('maintenance_providers', ['name' => 'Taller Los Santos']);
    }

    public function test_an_admin_can_edit_a_provider(): void
    {
        $provider = MaintenanceProvider::factory()->create(['name' => 'Old Name']);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('edit', $provider)
            ->assertSet('name', 'Old Name')
            ->set('name', 'New Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('New Name', $provider->fresh()->name);
    }

    public function test_an_admin_can_delete_a_provider(): void
    {
        $provider = MaintenanceProvider::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)->call('delete', $provider);

        $this->assertSoftDeleted($provider);
    }

    public function test_a_viewer_cannot_delete_a_provider(): void
    {
        $provider = MaintenanceProvider::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->call('delete', $provider)
            ->assertForbidden();

        $this->assertModelExists($provider);
    }
}
