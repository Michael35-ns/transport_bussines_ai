<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\StoreMaintenanceRequest;
use App\Http\Requests\UpdateMaintenanceRequest;
use App\Models\MaintenanceProvider;
use App\Models\Truck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * StoreMaintenanceRequest and UpdateMaintenanceRequest share identical field
 * rules via buildRules(). total is deliberately not a rule — it's always
 * computed from parts/labor/other cost, never entered directly.
 */
class MaintenanceRequestsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'truck_id' => Truck::factory()->create()->id,
            'type' => 'preventive',
            'cost_type_id' => null,
            'provider_id' => MaintenanceProvider::factory()->create()->id,
            'schedule_id' => null,
            'entry_date' => '2026-09-15',
            'completion_date' => '2026-09-16',
            'odometer' => 505000,
            'description' => 'Cambio de aceite y filtro',
            'parts_cost' => 30000,
            'labor_cost' => 15000,
            'other_cost' => 0,
            'downtime_days' => 0.5,
        ];
    }

    public function test_accepts_a_valid_payload(): void
    {
        $validator = Validator::make($this->validPayload(), StoreMaintenanceRequest::buildRules());

        $this->assertFalse($validator->fails());
    }

    public function test_requires_a_provider(): void
    {
        $data = $this->validPayload();
        unset($data['provider_id']);

        $validator = Validator::make($data, (new UpdateMaintenanceRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('provider_id', $validator->errors()->toArray());
    }

    public function test_rejects_a_completion_date_before_the_entry_date(): void
    {
        $data = $this->validPayload();
        $data['completion_date'] = '2026-09-10';

        $validator = Validator::make($data, StoreMaintenanceRequest::buildRules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('completion_date', $validator->errors()->toArray());
    }

    public function test_rejects_an_invalid_type(): void
    {
        $data = $this->validPayload();
        $data['type'] = 'scheduled';

        $validator = Validator::make($data, StoreMaintenanceRequest::buildRules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
    }
}
