<?php

namespace Tests\Feature\Policies;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\DriverWorklog;
use App\Models\FuelRecord;
use App\Models\FuelStation;
use App\Models\Maintenance;
use App\Models\MaintenanceProvider;
use App\Models\MaintenanceSchedule;
use App\Models\OverheadCost;
use App\Models\Route;
use App\Models\TollRecord;
use App\Models\Trip;
use App\Models\Truck;
use App\Models\TruckExpense;
use App\Models\TruckFixedCost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every model's policy in this list is a thin subclass of the shared
 * ModelPolicy (every role may read; only a non-viewer role may write —
 * docs/business/discovery.md §L.2 #7). Testing them together avoids
 * repeating the same assertions once per model, while looping over all of
 * them still exercises Laravel's policy auto-discovery for each one
 * individually.
 */
class ModelPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return list<class-string>
     */
    private function masterDataModels(): array
    {
        return [
            Truck::class, Driver::class, Customer::class, Route::class, Trip::class,
            MaintenanceProvider::class, MaintenanceSchedule::class, Maintenance::class,
            FuelStation::class, FuelRecord::class, TollRecord::class,
            TruckExpense::class, TruckFixedCost::class, OverheadCost::class, DriverWorklog::class,
        ];
    }

    public function test_every_role_can_view_master_data(): void
    {
        $viewer = User::factory()->viewer()->create();
        $admin = User::factory()->admin()->create();

        foreach ($this->masterDataModels() as $model) {
            $this->assertTrue($viewer->can('viewAny', $model));
            $this->assertTrue($admin->can('viewAny', $model));
        }
    }

    public function test_a_viewer_cannot_create_master_data(): void
    {
        $viewer = User::factory()->viewer()->create();

        foreach ($this->masterDataModels() as $model) {
            $this->assertFalse($viewer->can('create', $model));
        }
    }

    public function test_an_admin_or_owner_can_create_master_data(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->ownerAdmin()->create();

        foreach ($this->masterDataModels() as $model) {
            $this->assertTrue($admin->can('create', $model));
            $this->assertTrue($owner->can('create', $model));
        }
    }

    public function test_an_admin_can_update_and_delete_an_existing_record_of_each_model(): void
    {
        $admin = User::factory()->admin()->create();
        $records = [
            Truck::factory()->create(),
            Driver::factory()->create(),
            Customer::factory()->create(),
            Route::factory()->create(),
            Trip::factory()->create(),
            MaintenanceProvider::factory()->create(),
            MaintenanceSchedule::factory()->create(),
            Maintenance::factory()->create(),
            FuelStation::factory()->create(),
            FuelRecord::factory()->create(),
            TollRecord::factory()->create(),
            TruckExpense::factory()->create(),
            TruckFixedCost::factory()->create(),
            OverheadCost::factory()->create(),
            DriverWorklog::factory()->create(),
        ];

        foreach ($records as $record) {
            $this->assertTrue($admin->can('update', $record));
            $this->assertTrue($admin->can('delete', $record));
        }
    }

    public function test_a_viewer_cannot_update_or_delete_an_existing_record(): void
    {
        $viewer = User::factory()->viewer()->create();
        $truck = Truck::factory()->create();

        $this->assertFalse($viewer->can('update', $truck));
        $this->assertFalse($viewer->can('delete', $truck));
    }
}
