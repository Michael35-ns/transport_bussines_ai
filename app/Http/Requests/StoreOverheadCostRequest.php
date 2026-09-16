<?php

namespace App\Http\Requests;

use App\Enums\BillingCycle;
use App\Models\OverheadCost;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreOverheadCostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', OverheadCost::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return self::buildRules();
    }

    /**
     * Shared rules, reused by the Livewire component's own validate() call
     * so the Form Request stays the single source of truth. The pool this
     * feeds is allocated across trucks by OverheadAllocationService
     * (docs/decisions/0001-overhead-allocation-method.md), never entered
     * per truck here.
     *
     * @return array<string, mixed>
     */
    public static function buildRules(): array
    {
        return [
            'cost_type_id' => ['required', 'integer', 'exists:cost_types,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'billing_cycle' => ['required', new Enum(BillingCycle::class)],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ];
    }
}
