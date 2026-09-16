<?php

namespace Tests\Feature\Livewire\TruckFixedCosts;

use App\Enums\CostTypeScope;
use App\Livewire\TruckFixedCosts\Index;
use App\Models\CostType;
use App\Models\Truck;
use App\Models\TruckFixedCost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('truck-fixed-costs.index'))->assertRedirect(route('login'));
    }

    public function test_a_viewer_can_see_the_cost_list(): void
    {
        $truck = Truck::factory()->create(['plate' => 'SJB-123']);
        TruckFixedCost::factory()->create(['truck_id' => $truck->id]);

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)->assertSee('SJB-123');
    }

    public function test_an_admin_can_create_a_fixed_cost(): void
    {
        $truck = Truck::factory()->create();
        $costType = CostType::factory()->create(['scope' => CostTypeScope::TruckFixed]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('truck_id', (string) $truck->id)
            ->set('cost_type_id', (string) $costType->id)
            ->set('amount', 45000)
            ->set('billing_cycle', 'annual')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('truck_fixed_costs', ['truck_id' => $truck->id, 'amount' => '45000.00']);
    }

    public function test_creating_a_fixed_cost_rejects_an_effective_to_before_effective_from(): void
    {
        $truck = Truck::factory()->create();
        $costType = CostType::factory()->create(['scope' => CostTypeScope::TruckFixed]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('truck_id', (string) $truck->id)
            ->set('cost_type_id', (string) $costType->id)
            ->set('amount', 45000)
            ->set('effective_from', '2026-06-01')
            ->set('effective_to', '2026-01-01')
            ->call('save')
            ->assertHasErrors(['effective_to']);
    }

    public function test_a_viewer_cannot_create_a_fixed_cost(): void
    {
        $truck = Truck::factory()->create();
        $costType = CostType::factory()->create(['scope' => CostTypeScope::TruckFixed]);

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->set('truck_id', (string) $truck->id)
            ->set('cost_type_id', (string) $costType->id)
            ->set('amount', 45000)
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('truck_fixed_costs', ['truck_id' => $truck->id]);
    }

    public function test_an_admin_can_edit_a_fixed_cost(): void
    {
        $cost = TruckFixedCost::factory()->create(['amount' => 30000]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('edit', $cost)
            ->assertSet('amount', 30000.0)
            ->set('amount', 35000)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('35000.00', $cost->fresh()->amount);
    }

    public function test_an_admin_can_delete_a_fixed_cost(): void
    {
        $cost = TruckFixedCost::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)->call('delete', $cost);

        $this->assertModelMissing($cost);
    }

    public function test_a_viewer_cannot_delete_a_fixed_cost(): void
    {
        $cost = TruckFixedCost::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->call('delete', $cost)
            ->assertForbidden();

        $this->assertModelExists($cost);
    }
}
