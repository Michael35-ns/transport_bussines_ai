<?php

namespace Tests\Feature\Livewire\TruckExpenses;

use App\Enums\CostTypeScope;
use App\Livewire\TruckExpenses\Index;
use App\Models\CostType;
use App\Models\Truck;
use App\Models\TruckExpense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('truck-expenses.index'))->assertRedirect(route('login'));
    }

    public function test_a_viewer_can_see_the_expense_list(): void
    {
        $truck = Truck::factory()->create(['plate' => 'SJB-123']);
        TruckExpense::factory()->create(['truck_id' => $truck->id]);

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)->assertSee('SJB-123');
    }

    public function test_an_admin_can_create_an_expense(): void
    {
        $truck = Truck::factory()->create();
        $costType = CostType::factory()->create(['scope' => CostTypeScope::Expense]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('truck_id', (string) $truck->id)
            ->set('cost_type_id', (string) $costType->id)
            ->set('amount', 15000)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('truck_expenses', ['truck_id' => $truck->id, 'amount' => '15000.00']);
    }

    public function test_creating_an_expense_requires_a_cost_type(): void
    {
        $truck = Truck::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('truck_id', (string) $truck->id)
            ->set('cost_type_id', '')
            ->set('amount', 15000)
            ->call('save')
            ->assertHasErrors(['cost_type_id']);
    }

    public function test_a_viewer_cannot_create_an_expense(): void
    {
        $truck = Truck::factory()->create();
        $costType = CostType::factory()->create(['scope' => CostTypeScope::Expense]);

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->set('truck_id', (string) $truck->id)
            ->set('cost_type_id', (string) $costType->id)
            ->set('amount', 15000)
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('truck_expenses', ['truck_id' => $truck->id]);
    }

    public function test_an_admin_can_edit_an_expense(): void
    {
        $expense = TruckExpense::factory()->create(['amount' => 5000]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('edit', $expense)
            ->assertSet('amount', 5000.0)
            ->set('amount', 8000)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('8000.00', $expense->fresh()->amount);
    }

    public function test_an_admin_can_delete_an_expense(): void
    {
        $expense = TruckExpense::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)->call('delete', $expense);

        $this->assertModelMissing($expense);
    }

    public function test_a_viewer_cannot_delete_an_expense(): void
    {
        $expense = TruckExpense::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->call('delete', $expense)
            ->assertForbidden();

        $this->assertModelExists($expense);
    }
}
