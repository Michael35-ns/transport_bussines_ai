<?php

namespace Tests\Feature\Policies;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\Route;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TruckPolicy, DriverPolicy, CustomerPolicy, and RoutePolicy are thin
 * subclasses of the shared ModelPolicy (every role may read; only a
 * non-viewer role may write — docs/business/discovery.md §L.2 #7). Testing
 * them together avoids repeating the same assertions four times, while
 * looping over all four models still exercises Laravel's policy
 * auto-discovery for each one individually.
 */
class ModelPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return list<class-string>
     */
    private function masterDataModels(): array
    {
        return [Truck::class, Driver::class, Customer::class, Route::class];
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
