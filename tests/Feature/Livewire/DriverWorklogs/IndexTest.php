<?php

namespace Tests\Feature\Livewire\DriverWorklogs;

use App\Livewire\DriverWorklogs\Index;
use App\Models\Driver;
use App\Models\DriverWorklog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('driver-worklogs.index'))->assertRedirect(route('login'));
    }

    public function test_a_viewer_can_see_the_worklog_list(): void
    {
        $driver = Driver::factory()->create(['name' => 'Carlos Vargas']);
        DriverWorklog::factory()->create(['driver_id' => $driver->id]);

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)->assertSee('Carlos Vargas');
    }

    public function test_the_pay_preview_uses_the_drivers_current_hourly_rate(): void
    {
        $driver = Driver::factory()->create(['hourly_rate' => 1500]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('driver_id', (string) $driver->id)
            ->set('hours', 8)
            ->assertSet('payPreview', 12000.0);
    }

    public function test_an_admin_can_create_a_worklog_and_the_pay_is_snapshotted(): void
    {
        $driver = Driver::factory()->create(['hourly_rate' => 1500]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('driver_id', (string) $driver->id)
            ->set('work_date', '2026-09-15')
            ->set('hours', 8)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('driver_worklogs', [
            'driver_id' => $driver->id,
            'work_date' => '2026-09-15',
            'hourly_rate_snapshot' => '1500.0000',
            'computed_pay' => '12000.00',
        ]);
    }

    public function test_a_second_worklog_for_the_same_driver_and_day_is_rejected(): void
    {
        $driver = Driver::factory()->create();
        DriverWorklog::factory()->create(['driver_id' => $driver->id, 'work_date' => '2026-09-15']);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('driver_id', (string) $driver->id)
            ->set('work_date', '2026-09-15')
            ->set('hours', 8)
            ->call('save')
            ->assertHasErrors(['work_date']);
    }

    public function test_a_viewer_cannot_create_a_worklog(): void
    {
        $driver = Driver::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->set('driver_id', (string) $driver->id)
            ->set('hours', 8)
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('driver_worklogs', ['driver_id' => $driver->id]);
    }

    public function test_an_admin_can_edit_a_worklog(): void
    {
        $worklog = DriverWorklog::factory()->create(['hours' => 6]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('edit', $worklog)
            ->assertSet('hours', 6.0)
            ->set('hours', 7)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('7.00', $worklog->fresh()->hours);
    }

    public function test_an_admin_can_delete_a_worklog(): void
    {
        $worklog = DriverWorklog::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)->call('delete', $worklog);

        $this->assertModelMissing($worklog);
    }

    public function test_a_viewer_cannot_delete_a_worklog(): void
    {
        $worklog = DriverWorklog::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->call('delete', $worklog)
            ->assertForbidden();

        $this->assertModelExists($worklog);
    }
}
