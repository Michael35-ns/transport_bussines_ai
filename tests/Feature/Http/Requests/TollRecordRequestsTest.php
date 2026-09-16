<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\StoreTollRecordRequest;
use App\Http\Requests\UpdateTollRecordRequest;
use App\Models\Truck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * StoreTollRecordRequest and UpdateTollRecordRequest share identical field
 * rules via buildRules().
 */
class TollRecordRequestsTest extends TestCase
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
            'occurred_at' => '2026-09-15 08:00:00',
            'location' => 'Peaje San Marcos',
            'amount' => 1500,
            'payment_method' => 'cash',
        ];
    }

    public function test_accepts_a_valid_payload(): void
    {
        $validator = Validator::make($this->validPayload(), StoreTollRecordRequest::buildRules());

        $this->assertFalse($validator->fails());
    }

    public function test_requires_an_amount(): void
    {
        $data = $this->validPayload();
        unset($data['amount']);

        $validator = Validator::make($data, (new UpdateTollRecordRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('amount', $validator->errors()->toArray());
    }

    public function test_rejects_a_zero_amount(): void
    {
        $data = $this->validPayload();
        $data['amount'] = 0;

        $validator = Validator::make($data, StoreTollRecordRequest::buildRules());

        $this->assertTrue($validator->fails());
    }
}
