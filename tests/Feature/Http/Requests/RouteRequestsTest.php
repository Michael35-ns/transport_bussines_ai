<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\StoreRouteRequest;
use App\Http\Requests\UpdateRouteRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * StoreRouteRequest and UpdateRouteRequest share identical field rules. The
 * one rule that matters most here is standard_km being required — it's the
 * sole trip-distance source while no odometer is captured
 * (docs/decisions/0002-trip-distance-source.md).
 */
class RouteRequestsTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'name' => 'San Marcos - San Pablo',
            'origin' => 'San Marcos de Tarrazú',
            'destination' => 'San Pablo de León Cortés',
            'standard_km' => 18.5,
        ];
    }

    public function test_accepts_a_valid_payload(): void
    {
        $validator = Validator::make($this->validPayload(), (new StoreRouteRequest)->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_requires_standard_km(): void
    {
        $data = $this->validPayload();
        unset($data['standard_km']);

        $validator = Validator::make($data, (new StoreRouteRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('standard_km', $validator->errors()->toArray());
    }

    public function test_rejects_a_zero_standard_km(): void
    {
        $data = $this->validPayload();
        $data['standard_km'] = 0;

        $validator = Validator::make($data, (new UpdateRouteRequest)->rules());

        $this->assertTrue($validator->fails());
    }
}
