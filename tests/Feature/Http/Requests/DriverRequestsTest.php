<?php

namespace Tests\Feature\Http\Requests;

use App\Enums\ActiveStatus;
use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * StoreDriverRequest and UpdateDriverRequest share identical field rules —
 * tested together since there is no store/update behavioral difference here.
 */
class DriverRequestsTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'name' => 'Carlos Vargas',
            'hourly_rate' => 1200,
            'status' => ActiveStatus::Active->value,
        ];
    }

    public function test_accepts_a_valid_payload(): void
    {
        $validator = Validator::make($this->validPayload(), (new StoreDriverRequest)->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_requires_a_name(): void
    {
        $data = $this->validPayload();
        unset($data['name']);

        $validator = Validator::make($data, (new StoreDriverRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_requires_an_hourly_rate(): void
    {
        $data = $this->validPayload();
        unset($data['hourly_rate']);

        $validator = Validator::make($data, (new UpdateDriverRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('hourly_rate', $validator->errors()->toArray());
    }

    public function test_rejects_a_negative_hourly_rate(): void
    {
        $data = $this->validPayload();
        $data['hourly_rate'] = -5;

        $validator = Validator::make($data, (new StoreDriverRequest)->rules());

        $this->assertTrue($validator->fails());
    }
}
