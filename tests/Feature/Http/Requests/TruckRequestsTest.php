<?php

namespace Tests\Feature\Http\Requests;

use App\Enums\AcquisitionMode;
use App\Enums\ActiveStatus;
use App\Enums\VehicleType;
use App\Http\Requests\StoreTruckRequest;
use App\Models\Truck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * StoreTruckRequest and UpdateTruckRequest both delegate to
 * StoreTruckRequest::buildRules() (see app/Http/Requests/StoreTruckRequest.php),
 * so exercising that shared method covers both classes' validation surface.
 */
class TruckRequestsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'plate' => 'ABC-123',
            'vehicle_type' => VehicleType::FurgonSeco->value,
            'acquisition_mode' => AcquisitionMode::Owned->value,
            'status' => ActiveStatus::Active->value,
        ];
    }

    public function test_accepts_a_valid_payload(): void
    {
        $validator = Validator::make($this->validPayload(), StoreTruckRequest::buildRules());

        $this->assertFalse($validator->fails());
    }

    public function test_requires_a_vehicle_type(): void
    {
        $data = $this->validPayload();
        unset($data['vehicle_type']);

        $validator = Validator::make($data, StoreTruckRequest::buildRules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('vehicle_type', $validator->errors()->toArray());
    }

    public function test_rejects_a_vehicle_type_outside_the_enum(): void
    {
        $data = $this->validPayload();
        $data['vehicle_type'] = 'monster_truck';

        $validator = Validator::make($data, StoreTruckRequest::buildRules());

        $this->assertTrue($validator->fails());
    }

    public function test_rejects_a_plate_already_in_use(): void
    {
        Truck::factory()->create(['plate' => 'ABC-123']);

        $validator = Validator::make($this->validPayload(), StoreTruckRequest::buildRules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('plate', $validator->errors()->toArray());
    }

    public function test_update_ignores_the_current_trucks_own_plate(): void
    {
        $truck = Truck::factory()->create(['plate' => 'ABC-123']);

        $validator = Validator::make($this->validPayload(), StoreTruckRequest::buildRules(ignoring: $truck));

        $this->assertFalse($validator->fails());
    }

    public function test_update_rejects_another_trucks_plate(): void
    {
        Truck::factory()->create(['plate' => 'TAKEN-1']);
        $truck = Truck::factory()->create(['plate' => 'ABC-123']);

        $data = $this->validPayload();
        $data['plate'] = 'TAKEN-1';

        $validator = Validator::make($data, StoreTruckRequest::buildRules(ignoring: $truck));

        $this->assertTrue($validator->fails());
    }

    public function test_store_includes_current_odometer_but_update_does_not(): void
    {
        $this->assertArrayHasKey('current_odometer', StoreTruckRequest::buildRules(includeOdometer: true));
        $this->assertArrayNotHasKey('current_odometer', StoreTruckRequest::buildRules());
    }
}
