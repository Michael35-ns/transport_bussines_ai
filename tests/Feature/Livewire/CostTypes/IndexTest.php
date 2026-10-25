<?php

namespace Tests\Feature\Livewire\CostTypes;

use App\Enums\CostTypeScope;
use App\Livewire\CostTypes\Index;
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
        $this->get(route('cost-types.index'))->assertRedirect(route('login'));
    }

    public function test_a_viewer_can_see_the_category_list(): void
    {
        CostType::factory()->create(['name' => 'Vacaciones', 'scope' => CostTypeScope::Overhead]);

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)->assertSee('Vacaciones');
    }

    public function test_an_admin_can_create_a_category(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('name', 'Aguinaldo')
            ->set('scope', CostTypeScope::Overhead->value)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('cost_types', ['name' => 'Aguinaldo', 'scope' => 'overhead']);
    }

    public function test_a_category_name_must_be_unique_and_scope_valid(): void
    {
        CostType::factory()->create(['name' => 'Vacaciones']);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('name', 'Vacaciones')
            ->set('scope', 'nope')
            ->call('save')
            ->assertHasErrors(['name' => 'unique', 'scope']);
    }

    public function test_a_viewer_cannot_create_a_category(): void
    {
        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->set('name', 'Aguinaldo')
            ->set('scope', CostTypeScope::Overhead->value)
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('cost_types', ['name' => 'Aguinaldo']);
    }

    public function test_an_admin_can_rename_a_category(): void
    {
        $costType = CostType::factory()->create(['name' => 'Old', 'scope' => CostTypeScope::Expense]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('edit', $costType)
            ->assertSet('name', 'Old')
            ->set('name', 'New')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('New', $costType->fresh()->name);
    }

    public function test_the_scope_cannot_change_once_the_category_is_in_use(): void
    {
        $costType = CostType::factory()->create(['scope' => CostTypeScope::Overhead]);
        OverheadCost::factory()->create(['cost_type_id' => $costType->id]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('edit', $costType)
            ->set('scope', CostTypeScope::Expense->value)
            ->call('save')
            ->assertHasErrors('scope');

        $this->assertSame(CostTypeScope::Overhead, $costType->fresh()->scope);
    }

    public function test_an_unused_category_can_be_deleted(): void
    {
        $costType = CostType::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)->call('delete', $costType);

        $this->assertModelMissing($costType);
    }

    public function test_a_category_in_use_is_not_deleted(): void
    {
        $costType = CostType::factory()->create(['scope' => CostTypeScope::Overhead]);
        OverheadCost::factory()->create(['cost_type_id' => $costType->id]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)->call('delete', $costType);

        $this->assertModelExists($costType);
    }

    public function test_a_viewer_cannot_delete_a_category(): void
    {
        $costType = CostType::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->call('delete', $costType)
            ->assertForbidden();

        $this->assertModelExists($costType);
    }
}
