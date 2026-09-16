<?php

namespace Tests\Feature\Http\Requests;

use App\Enums\CostTypeScope;
use App\Http\Requests\StoreOverheadCostRequest;
use App\Http\Requests\UpdateOverheadCostRequest;
use App\Models\CostType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * StoreOverheadCostRequest and UpdateOverheadCostRequest share identical
 * field rules via buildRules().
 */
class OverheadCostRequestsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'cost_type_id' => CostType::factory()->create(['scope' => CostTypeScope::Overhead])->id,
            'amount' => 500000,
            'billing_cycle' => 'monthly',
            'effective_from' => '2026-01-01',
            'effective_to' => null,
        ];
    }

    public function test_accepts_a_valid_payload(): void
    {
        $validator = Validator::make($this->validPayload(), StoreOverheadCostRequest::buildRules());

        $this->assertFalse($validator->fails());
    }

    public function test_requires_a_cost_type(): void
    {
        $data = $this->validPayload();
        unset($data['cost_type_id']);

        $validator = Validator::make($data, (new UpdateOverheadCostRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cost_type_id', $validator->errors()->toArray());
    }

    public function test_rejects_an_invalid_billing_cycle(): void
    {
        $data = $this->validPayload();
        $data['billing_cycle'] = 'weekly';

        $validator = Validator::make($data, StoreOverheadCostRequest::buildRules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('billing_cycle', $validator->errors()->toArray());
    }
}
