<?php

namespace Tests\Feature\Http\Requests;

use App\Enums\CostTypeScope;
use App\Http\Requests\StoreTruckFixedCostRequest;
use App\Http\Requests\UpdateTruckFixedCostRequest;
use App\Models\CostType;
use App\Models\Truck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * StoreTruckFixedCostRequest and UpdateTruckFixedCostRequest share identical
 * field rules via buildRules().
 */
class TruckFixedCostRequestsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'truck_id' => Truck::factory()->create()->id,
            'cost_type_id' => CostType::factory()->create(['scope' => CostTypeScope::TruckFixed])->id,
            'amount' => 45000,
            'billing_cycle' => 'annual',
            'effective_from' => '2026-01-01',
            'effective_to' => null,
        ];
    }

    public function test_accepts_a_valid_payload(): void
    {
        $validator = Validator::make($this->validPayload(), StoreTruckFixedCostRequest::buildRules());

        $this->assertFalse($validator->fails());
    }

    public function test_requires_a_billing_cycle(): void
    {
        $data = $this->validPayload();
        unset($data['billing_cycle']);

        $validator = Validator::make($data, (new UpdateTruckFixedCostRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('billing_cycle', $validator->errors()->toArray());
    }

    public function test_rejects_an_effective_to_before_effective_from(): void
    {
        $data = $this->validPayload();
        $data['effective_to'] = '2025-01-01';

        $validator = Validator::make($data, StoreTruckFixedCostRequest::buildRules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('effective_to', $validator->errors()->toArray());
    }
}
