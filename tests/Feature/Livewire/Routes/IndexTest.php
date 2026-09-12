<?php

namespace Tests\Feature\Livewire\Routes;

use App\Livewire\Routes\Index;
use App\Models\Route;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('routes.index'))->assertRedirect(route('login'));
    }

    public function test_a_viewer_can_see_the_route_list(): void
    {
        Route::factory()->create(['name' => 'San Marcos - San Pablo']);

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)->assertSee('San Marcos - San Pablo');
    }

    public function test_an_admin_can_create_a_route(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('name', 'San Marcos - San Pablo')
            ->set('origin', 'San Marcos de Tarrazú')
            ->set('destination', 'San Pablo de León Cortés')
            ->set('standard_km', 18.5)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('routes', ['name' => 'San Marcos - San Pablo', 'standard_km' => 18.5]);
    }

    public function test_creating_a_route_requires_standard_km(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('name', 'San Marcos - San Pablo')
            ->set('origin', 'San Marcos')
            ->set('destination', 'San Pablo')
            ->set('standard_km', null)
            ->call('save')
            ->assertHasErrors(['standard_km' => 'required']);
    }

    public function test_a_viewer_cannot_create_a_route(): void
    {
        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->set('name', 'San Marcos - San Pablo')
            ->set('origin', 'San Marcos')
            ->set('destination', 'San Pablo')
            ->set('standard_km', 18.5)
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('routes', ['name' => 'San Marcos - San Pablo']);
    }

    public function test_an_admin_can_edit_a_route(): void
    {
        $route = Route::factory()->create(['standard_km' => 10]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('edit', $route)
            ->set('standard_km', 25.5)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('25.50', $route->fresh()->standard_km);
    }

    public function test_an_admin_can_delete_a_route(): void
    {
        $route = Route::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)->call('delete', $route);

        $this->assertSoftDeleted($route);
    }

    public function test_a_viewer_cannot_delete_a_route(): void
    {
        $route = Route::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->call('delete', $route)
            ->assertForbidden();

        $this->assertModelExists($route);
    }
}
