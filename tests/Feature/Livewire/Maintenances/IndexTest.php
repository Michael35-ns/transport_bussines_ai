<?php

namespace Tests\Feature\Livewire\Maintenances;

use App\Enums\MaintenanceType;
use App\Livewire\Maintenances\Index;
use App\Models\Maintenance;
use App\Models\MaintenanceProvider;
use App\Models\MaintenanceSchedule;
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
        $this->get(route('maintenances.index'))->assertRedirect(route('login'));
    }

    public function test_a_viewer_can_see_the_maintenance_list(): void
    {
        $truck = Truck::factory()->create(['plate' => 'SJB-123']);
        Maintenance::factory()->create(['truck_id' => $truck->id]);

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)->assertSee('SJB-123');
    }

    public function test_the_total_is_computed_from_the_three_cost_fields(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('parts_cost', 30000)
            ->set('labor_cost', 15000)
            ->set('other_cost', 5000)
            ->assertSet('totalPreview', 50000.0);
    }

    public function test_an_admin_can_create_a_preventive_maintenance_that_closes_a_schedule(): void
    {
        $truck = Truck::factory()->create();
        $provider = MaintenanceProvider::factory()->create();
        $schedule = MaintenanceSchedule::factory()->create(['truck_id' => $truck->id, 'last_done_odometer' => 495000]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('truck_id', (string) $truck->id)
            ->set('provider_id', (string) $provider->id)
            ->set('schedule_id', (string) $schedule->id)
            ->set('odometer', 505000)
            ->set('parts_cost', 30000)
            ->set('labor_cost', 15000)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('maintenance', [
            'truck_id' => $truck->id,
            'schedule_id' => $schedule->id,
            'total' => '45000.00',
        ]);
        $this->assertSame('505000.00', $truck->fresh()->current_odometer);
        $this->assertSame('505000.00', $schedule->fresh()->last_done_odometer);
    }

    public function test_creating_a_maintenance_requires_a_provider(): void
    {
        $truck = Truck::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('truck_id', (string) $truck->id)
            ->set('provider_id', '')
            ->call('save')
            ->assertHasErrors(['provider_id']);
    }

    public function test_a_viewer_cannot_create_a_maintenance(): void
    {
        $truck = Truck::factory()->create();
        $provider = MaintenanceProvider::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->set('truck_id', (string) $truck->id)
            ->set('provider_id', (string) $provider->id)
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('maintenance', ['truck_id' => $truck->id]);
    }

    public function test_an_admin_can_edit_a_maintenance(): void
    {
        $maintenance = Maintenance::factory()->create(['description' => 'Old description', 'type' => MaintenanceType::Preventive]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('edit', $maintenance)
            ->assertSet('description', 'Old description')
            ->set('description', 'New description')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('New description', $maintenance->fresh()->description);
    }

    public function test_an_admin_can_delete_a_maintenance(): void
    {
        $maintenance = Maintenance::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)->call('delete', $maintenance);

        $this->assertModelMissing($maintenance);
    }

    public function test_a_viewer_cannot_delete_a_maintenance(): void
    {
        $maintenance = Maintenance::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->call('delete', $maintenance)
            ->assertForbidden();

        $this->assertModelExists($maintenance);
    }
}
