<?php

namespace Tests\Feature\Services\Maintenance;

use App\Enums\MaintenanceType;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Models\Truck;
use App\Services\Maintenance\OdometerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * docs/decisions/0002-trip-distance-source.md: every service visit's real
 * odometer re-anchors trucks.current_odometer, and (when tied to a
 * preventive schedule) resets that schedule's last_done_odometer so the
 * next due point counts from the service just performed.
 */
class OdometerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reanchors_the_truck_current_odometer_to_the_reading(): void
    {
        $truck = Truck::factory()->create(['current_odometer' => 100]);
        $maintenance = Maintenance::factory()->create([
            'truck_id' => $truck->id,
            'odometer' => 505000,
            'completion_date' => now(),
        ]);

        (new OdometerService)->applyReading($maintenance->fresh());

        $this->assertSame('505000.00', $truck->fresh()->current_odometer);
    }

    public function test_it_does_not_regress_the_anchor_when_an_older_record_is_entered_later(): void
    {
        $truck = Truck::factory()->create(['current_odometer' => 100]);
        Maintenance::factory()->create([
            'truck_id' => $truck->id,
            'odometer' => 510000,
            'completion_date' => now(),
        ]);
        $olderBackfilled = Maintenance::factory()->create([
            'truck_id' => $truck->id,
            'odometer' => 505000,
            'completion_date' => now()->subMonth(),
        ]);

        (new OdometerService)->applyReading($olderBackfilled->fresh());

        $this->assertSame('510000.00', $truck->fresh()->current_odometer);
    }

    public function test_it_does_nothing_when_the_maintenance_has_no_odometer(): void
    {
        $truck = Truck::factory()->create(['current_odometer' => 100]);
        $maintenance = Maintenance::factory()->create(['truck_id' => $truck->id, 'odometer' => null]);

        (new OdometerService)->applyReading($maintenance);

        $this->assertSame('100.00', $truck->fresh()->current_odometer);
    }

    public function test_it_resets_the_preventive_schedules_last_done_odometer(): void
    {
        $truck = Truck::factory()->create();
        $schedule = MaintenanceSchedule::factory()->create(['truck_id' => $truck->id, 'last_done_odometer' => 495000]);
        $maintenance = Maintenance::factory()->create([
            'truck_id' => $truck->id,
            'type' => MaintenanceType::Preventive,
            'schedule_id' => $schedule->id,
            'odometer' => 505000,
        ]);

        (new OdometerService)->applyReading($maintenance->fresh());

        $this->assertSame('505000.00', $schedule->fresh()->last_done_odometer);
    }

    public function test_a_corrective_repair_never_resets_a_schedule_even_if_one_is_linked(): void
    {
        $truck = Truck::factory()->create();
        $schedule = MaintenanceSchedule::factory()->create(['truck_id' => $truck->id, 'last_done_odometer' => 495000]);
        $maintenance = Maintenance::factory()->create([
            'truck_id' => $truck->id,
            'type' => MaintenanceType::Corrective,
            'schedule_id' => $schedule->id,
            'odometer' => 505000,
        ]);

        (new OdometerService)->applyReading($maintenance->fresh());

        $this->assertSame('495000.00', $schedule->fresh()->last_done_odometer);
    }
}
