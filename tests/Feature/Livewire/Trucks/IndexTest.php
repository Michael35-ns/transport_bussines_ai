<?php

namespace Tests\Feature\Livewire\Trucks;

use App\Enums\AcquisitionMode;
use App\Enums\ActiveStatus;
use App\Enums\VehicleType;
use App\Livewire\Trucks\Index;
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
        $this->get(route('trucks.index'))->assertRedirect(route('login'));
    }

    public function test_a_viewer_can_see_the_truck_list(): void
    {
        $truck = Truck::factory()->create(['plate' => 'ABC-123']);

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->assertSee('ABC-123');
    }

    public function test_the_search_field_filters_by_plate(): void
    {
        Truck::factory()->create(['plate' => 'ABC-123']);
        Truck::factory()->create(['plate' => 'ZZZ-999']);

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->set('search', 'ABC')
            ->assertSee('ABC-123')
            ->assertDontSee('ZZZ-999');
    }

    public function test_an_admin_can_create_a_truck(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('plate', 'NEW-001')
            ->set('vehicle_type', VehicleType::FurgonSeco->value)
            ->set('acquisition_mode', AcquisitionMode::Owned->value)
            ->set('status', ActiveStatus::Active->value)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('trucks', ['plate' => 'NEW-001']);
    }

    public function test_creating_a_truck_requires_a_vehicle_type(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('plate', 'NEW-001')
            ->set('vehicle_type', '')
            ->call('save')
            ->assertHasErrors(['vehicle_type' => 'required']);
    }

    public function test_a_viewer_cannot_create_a_truck(): void
    {
        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->set('plate', 'NEW-001')
            ->set('vehicle_type', VehicleType::FurgonSeco->value)
            ->set('acquisition_mode', AcquisitionMode::Owned->value)
            ->set('status', ActiveStatus::Active->value)
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('trucks', ['plate' => 'NEW-001']);
    }

    public function test_an_admin_can_edit_a_truck(): void
    {
        $truck = Truck::factory()->create(['plate' => 'OLD-001']);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('edit', $truck)
            ->assertSet('plate', 'OLD-001')
            ->set('plate', 'UPDATED-001')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('UPDATED-001', $truck->fresh()->plate);
    }

    public function test_editing_a_truck_ignores_its_own_plate_for_uniqueness(): void
    {
        $truck = Truck::factory()->create(['plate' => 'SAME-001']);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('edit', $truck)
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_an_admin_can_delete_a_truck(): void
    {
        $truck = Truck::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('delete', $truck);

        $this->assertSoftDeleted($truck);
    }

    public function test_a_viewer_cannot_delete_a_truck(): void
    {
        $truck = Truck::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->call('delete', $truck)
            ->assertForbidden();

        $this->assertModelExists($truck);
    }
}
