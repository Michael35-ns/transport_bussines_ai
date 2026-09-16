<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\StoreFuelStationRequest;
use App\Http\Requests\UpdateFuelStationRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * StoreFuelStationRequest and UpdateFuelStationRequest share identical field
 * rules.
 */
class FuelStationRequestsTest extends TestCase
{
    public function test_accepts_a_payload_with_only_a_name(): void
    {
        $validator = Validator::make(['name' => 'Servicentro Los Santos'], (new StoreFuelStationRequest)->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_requires_a_name(): void
    {
        $validator = Validator::make([], (new UpdateFuelStationRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }
}
