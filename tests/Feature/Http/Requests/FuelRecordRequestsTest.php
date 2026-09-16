<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\StoreFuelRecordRequest;
use App\Http\Requests\UpdateFuelRecordRequest;
use App\Models\FuelStation;
use App\Models\Truck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * StoreFuelRecordRequest and UpdateFuelRecordRequest share identical field
 * rules via buildRules(). total is deliberately not a rule — it's always
 * computed as liters * unit_price, never entered directly.
 */
class FuelRecordRequestsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'truck_id' => Truck::factory()->create()->id,
            'trip_id' => null,
            'fuel_station_id' => FuelStation::factory()->create()->id,
            'occurred_at' => '2026-09-15 08:00:00',
            'liters' => 80,
            'unit_price' => 750,
            'payment_method' => 'cash',
        ];
    }

    public function test_accepts_a_valid_payload(): void
    {
        $validator = Validator::make($this->validPayload(), StoreFuelRecordRequest::buildRules());

        $this->assertFalse($validator->fails());
    }

    public function test_requires_a_fuel_station(): void
    {
        $data = $this->validPayload();
        unset($data['fuel_station_id']);

        $validator = Validator::make($data, (new UpdateFuelRecordRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('fuel_station_id', $validator->errors()->toArray());
    }

    public function test_rejects_zero_liters(): void
    {
        $data = $this->validPayload();
        $data['liters'] = 0;

        $validator = Validator::make($data, StoreFuelRecordRequest::buildRules());

        $this->assertTrue($validator->fails());
    }

    public function test_rejects_an_invalid_payment_method(): void
    {
        $data = $this->validPayload();
        $data['payment_method'] = 'crypto';

        $validator = Validator::make($data, StoreFuelRecordRequest::buildRules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('payment_method', $validator->errors()->toArray());
    }
}
