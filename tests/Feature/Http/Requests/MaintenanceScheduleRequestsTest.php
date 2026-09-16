<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\StoreMaintenanceScheduleRequest;
use App\Http\Requests\UpdateMaintenanceScheduleRequest;
use App\Models\Truck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * StoreMaintenanceScheduleRequest and UpdateMaintenanceScheduleRequest share
 * identical field rules via buildRules().
 */
class MaintenanceScheduleRequestsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'truck_id' => Truck::factory()->create()->id,
            'task_name' => 'Cambio de aceite',
            'interval_type' => 'km',
            'interval_value' => 5000,
            'last_done_odometer' => null,
            'lead_km' => 500,
            'active' => true,
        ];
    }

    public function test_accepts_a_valid_payload(): void
    {
        $validator = Validator::make($this->validPayload(), StoreMaintenanceScheduleRequest::buildRules());

        $this->assertFalse($validator->fails());
    }

    public function test_requires_a_task_name(): void
    {
        $data = $this->validPayload();
        unset($data['task_name']);

        $validator = Validator::make($data, (new UpdateMaintenanceScheduleRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('task_name', $validator->errors()->toArray());
    }

    public function test_rejects_an_invalid_interval_type(): void
    {
        $data = $this->validPayload();
        $data['interval_type'] = 'weeks';

        $validator = Validator::make($data, StoreMaintenanceScheduleRequest::buildRules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('interval_type', $validator->errors()->toArray());
    }

    public function test_requires_a_valid_truck(): void
    {
        $data = $this->validPayload();
        $data['truck_id'] = 999999;

        $validator = Validator::make($data, StoreMaintenanceScheduleRequest::buildRules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('truck_id', $validator->errors()->toArray());
    }
}
