<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\StoreMaintenanceProviderRequest;
use App\Http\Requests\UpdateMaintenanceProviderRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * StoreMaintenanceProviderRequest and UpdateMaintenanceProviderRequest share
 * identical field rules.
 */
class MaintenanceProviderRequestsTest extends TestCase
{
    public function test_accepts_a_payload_with_only_a_name(): void
    {
        $validator = Validator::make(['name' => 'Taller Los Santos'], (new StoreMaintenanceProviderRequest)->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_requires_a_name(): void
    {
        $validator = Validator::make([], (new UpdateMaintenanceProviderRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }
}
