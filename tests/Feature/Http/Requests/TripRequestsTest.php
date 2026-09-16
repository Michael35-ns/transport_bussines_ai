<?php

namespace Tests\Feature\Http\Requests;

use App\Enums\TripStatus;
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
            'planned_start' => '2026-09-15 08:00:00',
            'actual_start' => '2026-09-15 08:10:00',
            'actual_end' => '2026-09-15 12:00:00',
            'distance' => 45.5,
            'distance_estimated' => true,
            'price' => 65000,
            'status' => TripStatus::Completed->value,
        ];
    }

    public function test_accepts_a_valid_payload(): void
    {
        $validator = Validator::make($this->validPayload(), StoreTripRequest::buildRules(TripStatus::Completed->value));

        $this->assertFalse($validator->fails());
    }

    public function test_requires_distance(): void
    {
        $data = $this->validPayload();
        unset($data['distance']);

        $validator = Validator::make($data, StoreTripRequest::buildRules(TripStatus::Completed->value));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('distance', $validator->errors()->toArray());
    }

    public function test_requires_price(): void
    {
        $data = $this->validPayload();
        unset($data['price']);

        $validator = Validator::make($data, StoreTripRequest::buildRules(TripStatus::Completed->value));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('price', $validator->errors()->toArray());
    }

    public function test_a_completed_trip_requires_an_actual_end(): void
    {
        $data = $this->validPayload();
        unset($data['actual_end']);

        $validator = Validator::make($data, StoreTripRequest::buildRules(TripStatus::Completed->value));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('actual_end', $validator->errors()->toArray());
    }

    public function test_a_planned_trip_does_not_require_an_actual_end(): void
    {
        $data = $this->validPayload();
        $data['status'] = TripStatus::Planned->value;
        unset($data['actual_start'], $data['actual_end']);

        $validator = Validator::make($data, StoreTripRequest::buildRules(TripStatus::Planned->value));

        $this->assertFalse($validator->fails());
    }

    public function test_rejects_an_actual_end_before_the_actual_start(): void
    {
        $data = $this->validPayload();
        $data['actual_end'] = '2026-09-15 07:00:00';

        $validator = Validator::make($data, StoreTripRequest::buildRules(TripStatus::Completed->value));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('actual_end', $validator->errors()->toArray());
    }
}
