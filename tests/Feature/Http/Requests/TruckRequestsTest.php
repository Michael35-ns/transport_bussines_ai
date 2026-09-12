<?php

namespace Tests\Feature\Http\Requests;

use App\Enums\AcquisitionMode;
use App\Enums\ActiveStatus;
use App\Enums\VehicleType;
use App\Http\Requests\StoreTruckRequest;
use App\Http\Requests\UpdateTruckRequest;
use App\Models\Truck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * StoreTruckRequest and UpdateTruckRequest share the same field rules except
 * for the plate/internal_no uniqueness check, which must ignore the record
 * being edited — tested together to cover that difference directly.
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

    public function test_store_accepts_a_valid_payload(): void
    {
        $validator = Validator::make($this->validPayload(), (new StoreTruckRequest)->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_store_requires_a_vehicle_type(): void
    {
        $data = $this->validPayload();
        unset($data['vehicle_type']);

        $validator = Validator::make($data, (new StoreTruckRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('vehicle_type', $validator->errors()->toArray());
    }

    public function test_store_rejects_a_vehicle_type_outside_the_enum(): void
    {
        $data = $this->validPayload();
        $data['vehicle_type'] = 'monster_truck';

        $validator = Validator::make($data, (new StoreTruckRequest)->rules());

        $this->assertTrue($validator->fails());
    }

    public function test_store_rejects_a_plate_already_in_use(): void
    {
        Truck::factory()->create(['plate' => 'ABC-123']);

        $validator = Validator::make($this->validPayload(), (new StoreTruckRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('plate', $validator->errors()->toArray());
    }

    public function test_update_ignores_the_current_trucks_own_plate(): void
    {
        $truck = Truck::factory()->create(['plate' => 'ABC-123']);

        $validator = Validator::make($this->validPayload(), $this->updateRequestFor($truck)->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_update_rejects_another_trucks_plate(): void
    {
        Truck::factory()->create(['plate' => 'TAKEN-1']);
        $truck = Truck::factory()->create(['plate' => 'ABC-123']);

        $data = $this->validPayload();
        $data['plate'] = 'TAKEN-1';

        $validator = Validator::make($data, $this->updateRequestFor($truck)->rules());

        $this->assertTrue($validator->fails());
    }

    /**
     * UpdateTruckRequest reads the bound route model via $this->route('truck')
     * to build the unique-ignore rule. Bind it directly on the request
     * instance rather than issuing a real HTTP call.
     */
    private function updateRequestFor(Truck $truck): UpdateTruckRequest
    {
        $request = UpdateTruckRequest::create("/trucks/{$truck->id}", 'PUT');

        $route = new RoutingRoute('PUT', '/trucks/{truck}', []);
        $route->bind($request);
        $route->setParameter('truck', $truck);

        return $request->setRouteResolver(fn () => $route);
    }
}
