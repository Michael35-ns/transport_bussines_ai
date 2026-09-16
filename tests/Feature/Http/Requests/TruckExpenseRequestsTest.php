<?php

namespace Tests\Feature\Http\Requests;

use App\Enums\CostTypeScope;
use App\Http\Requests\StoreTruckExpenseRequest;
use App\Http\Requests\UpdateTruckExpenseRequest;
use App\Models\CostType;
use App\Models\Truck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * StoreTruckExpenseRequest and UpdateTruckExpenseRequest share identical
 * field rules via buildRules().
 */
class TruckExpenseRequestsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'truck_id' => Truck::factory()->create()->id,
            'cost_type_id' => CostType::factory()->create(['scope' => CostTypeScope::Expense])->id,
            'expense_date' => '2026-09-15',
            'amount' => 15000,
            'description' => 'Lavado',
        ];
    }

    public function test_accepts_a_valid_payload(): void
    {
        $validator = Validator::make($this->validPayload(), StoreTruckExpenseRequest::buildRules());

        $this->assertFalse($validator->fails());
    }

    public function test_requires_a_cost_type(): void
    {
        $data = $this->validPayload();
        unset($data['cost_type_id']);

        $validator = Validator::make($data, (new UpdateTruckExpenseRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cost_type_id', $validator->errors()->toArray());
    }

    public function test_rejects_a_zero_amount(): void
    {
        $data = $this->validPayload();
        $data['amount'] = 0;

        $validator = Validator::make($data, StoreTruckExpenseRequest::buildRules());

        $this->assertTrue($validator->fails());
    }
}
