<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\StoreTripRequest;
use App\Models\Driver;
use App\Models\Route;
use App\Models\Truck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * StoreTripRequest::buildRules() is shared verbatim by UpdateTripRequest and
 * by the Trips Livewire component, so testing it here covers all three.
 * status/actual_end are deliberately not fields — the component always sets
 * them itself (every trip is captured after the fact).
 */
class TripRequestsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'truck_id' => Truck::factory()->create()->id,
            'driver_id' => Driver::factory()->create()->id,
            'route_id' => Route::factory()->create()->id,
            'rate_agreement_id' => null,
            'distance' => 45.5,
            'distance_estimated' => true,
            'price' => 65000,
        ];
    }

    public function test_accepts_a_valid_payload(): void
    {
        $validator = Validator::make($this->validPayload(), StoreTripRequest::buildRules());

        $this->assertFalse($validator->fails());
    }

    public function test_requires_distance(): void
    {
        $data = $this->validPayload();
        unset($data['distance']);

        $validator = Validator::make($data, StoreTripRequest::buildRules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('distance', $validator->errors()->toArray());
    }

    public function test_requires_price(): void
    {
        $data = $this->validPayload();
        unset($data['price']);

        $validator = Validator::make($data, StoreTripRequest::buildRules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('price', $validator->errors()->toArray());
    }

    public function test_rejects_a_zero_distance(): void
    {
        $data = $this->validPayload();
        $data['distance'] = 0;

        $validator = Validator::make($data, StoreTripRequest::buildRules());

        $this->assertTrue($validator->fails());
    }
}
