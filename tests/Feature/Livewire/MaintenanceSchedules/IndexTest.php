<?php

namespace Tests\Feature\Livewire\MaintenanceSchedules;

use App\Livewire\MaintenanceSchedules\Index;
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
        $this->get(route('maintenance-schedules.index'))->assertRedirect(route('login'));
    }

    public function test_a_viewer_can_see_the_schedule_list_with_its_computed_status(): void
    {
        $truck = Truck::factory()->create(['plate' => 'SJB-123', 'current_odometer' => 505000]);
        MaintenanceSchedule::factory()->create([
            'truck_id' => $truck->id,
            'last_done_odometer' => 500000,
            'interval_value' => 5000,
            'lead_km' => 500,
        ]);

        $this->actingAs(User::factory()->viewer()->create());

        // 505,000 is within lead_km (500) of the 505,000 due point? No —
        // due = 500,000 + 5,000 = 505,000, and current = 505,000, so it's
        // exactly due: VENCIDO.
        Livewire::test(Index::class)->assertSee('SJB-123')->assertSee('VENCIDO');
    }

    public function test_an_admin_can_create_a_schedule(): void
    {
        $truck = Truck::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('truck_id', (string) $truck->id)
            ->set('task_name', 'Cambio de aceite')
            ->set('interval_value', 5000)
            ->set('lead_km', 500)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('maintenance_schedule', [
            'truck_id' => $truck->id,
            'task_name' => 'Cambio de aceite',
        ]);
    }

    public function test_creating_a_schedule_requires_a_truck(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('truck_id', '')
            ->call('save')
            ->assertHasErrors(['truck_id' => 'required']);
    }

    public function test_a_viewer_cannot_create_a_schedule(): void
    {
        $truck = Truck::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->set('truck_id', (string) $truck->id)
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('maintenance_schedule', ['truck_id' => $truck->id]);
    }

    public function test_an_admin_can_edit_a_schedule(): void
    {
        $schedule = MaintenanceSchedule::factory()->create(['task_name' => 'Old task']);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('edit', $schedule)
            ->assertSet('task_name', 'Old task')
            ->set('task_name', 'New task')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('New task', $schedule->fresh()->task_name);
    }

    public function test_an_admin_can_delete_a_schedule(): void
    {
        $schedule = MaintenanceSchedule::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)->call('delete', $schedule);

        $this->assertModelMissing($schedule);
    }

    public function test_a_viewer_cannot_delete_a_schedule(): void
    {
        $schedule = MaintenanceSchedule::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->call('delete', $schedule)
            ->assertForbidden();

        $this->assertModelExists($schedule);
    }
}
