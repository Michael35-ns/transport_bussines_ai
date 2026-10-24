<?php

namespace Tests\Feature\Livewire\OverheadCosts;

use App\Enums\CostTypeScope;
use App\Livewire\OverheadCosts\Index;
use App\Models\CostType;
use App\Models\OverheadCost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('overhead-costs.index'))->assertRedirect(route('login'));
    }

    public function test_a_viewer_can_see_the_cost_list(): void
    {
        $costType = CostType::factory()->create(['scope' => CostTypeScope::Overhead, 'name' => 'Salarios']);
        OverheadCost::factory()->create(['cost_type_id' => $costType->id]);

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)->assertSee('Salarios');
    }

    public function test_an_admin_can_create_an_overhead_cost(): void
    {
        $costType = CostType::factory()->create(['scope' => CostTypeScope::Overhead]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('cost_type_id', (string) $costType->id)
            ->set('amount', 500000)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('overhead_costs', ['cost_type_id' => $costType->id, 'amount' => '500000.00']);
    }

    public function test_creating_an_overhead_cost_requires_a_cost_type(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('cost_type_id', '')
            ->set('amount', 500000)
            ->call('save')
            ->assertHasErrors(['cost_type_id']);
    }

    public function test_a_viewer_cannot_create_an_overhead_cost(): void
    {
        $costType = CostType::factory()->create(['scope' => CostTypeScope::Overhead]);

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->set('cost_type_id', (string) $costType->id)
            ->set('amount', 500000)
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('overhead_costs', ['cost_type_id' => $costType->id]);
    }

    public function test_an_admin_can_edit_an_overhead_cost(): void
    {
        $cost = OverheadCost::factory()->create(['amount' => 400000]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('edit', $cost)
            ->assertSet('amount', 400000.0)
            ->set('amount', 450000)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('450000.00', $cost->fresh()->amount);
    }

    public function test_an_admin_can_delete_an_overhead_cost(): void
    {
        $cost = OverheadCost::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)->call('delete', $cost);

        $this->assertModelMissing($cost);
    }

    public function test_a_viewer_cannot_delete_an_overhead_cost(): void
    {
        $cost = OverheadCost::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->call('delete', $cost)
            ->assertForbidden();

        $this->assertModelExists($cost);
    }
}
