<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * StoreCustomerRequest and UpdateCustomerRequest share identical field rules.
 */
class CustomerRequestsTest extends TestCase
{
    public function test_accepts_a_payload_with_only_a_name(): void
    {
        $validator = Validator::make(['name' => 'Walmart CoopeDota'], (new StoreCustomerRequest)->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_requires_a_name(): void
    {
        $validator = Validator::make([], (new UpdateCustomerRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_rejects_a_negative_credit_days(): void
    {
        $validator = Validator::make(
            ['name' => 'Auto Mercado', 'credit_days' => -1],
            (new StoreCustomerRequest)->rules()
        );

        $this->assertTrue($validator->fails());
    }
}
