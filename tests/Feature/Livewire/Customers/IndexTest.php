<?php

namespace Tests\Feature\Livewire\Customers;

use App\Livewire\Customers\Index;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('customers.index'))->assertRedirect(route('login'));
    }

    public function test_a_viewer_can_see_the_customer_list(): void
    {
        Customer::factory()->create(['name' => 'Walmart CoopeDota']);

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)->assertSee('Walmart CoopeDota');
    }

    public function test_the_search_field_filters_by_name(): void
    {
        Customer::factory()->create(['name' => 'Walmart CoopeDota']);
        Customer::factory()->create(['name' => 'Auto Mercado']);

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->set('search', 'Walmart')
            ->assertSee('Walmart CoopeDota')
            ->assertDontSee('Auto Mercado');
    }

    public function test_an_admin_can_create_a_customer(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('name', 'Megasuper')
            ->set('credit_days', 8)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('customers', ['name' => 'Megasuper']);
    }

    public function test_creating_a_customer_requires_a_name(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_a_viewer_cannot_create_a_customer(): void
    {
        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->set('name', 'Megasuper')
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('customers', ['name' => 'Megasuper']);
    }

    public function test_an_admin_can_edit_a_customer(): void
    {
        $customer = Customer::factory()->create(['name' => 'Old Name']);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('edit', $customer)
            ->assertSet('name', 'Old Name')
            ->set('name', 'New Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('New Name', $customer->fresh()->name);
    }

    public function test_an_admin_can_delete_a_customer(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)->call('delete', $customer);

        $this->assertSoftDeleted($customer);
    }

    public function test_a_viewer_cannot_delete_a_customer(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->call('delete', $customer)
            ->assertForbidden();

        $this->assertModelExists($customer);
    }
}
