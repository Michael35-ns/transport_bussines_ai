<?php

namespace App\Http\Requests;

use App\Enums\BillingCycle;
use App\Models\TruckFixedCost;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTruckFixedCostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', TruckFixedCost::class) ?? false;
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
     * so the Form Request stays the single source of truth. The real billed
     * amount/cycle here — weekly figures are always derived by proration
     * (docs/finance/financial-model.md §J.3), never stored.
     *
     * @return array<string, mixed>
     */
    public static function buildRules(): array
    {
        return [
            'truck_id' => ['required', 'integer', 'exists:trucks,id'],
            'cost_type_id' => ['required', 'integer', 'exists:cost_types,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'billing_cycle' => ['required', new Enum(BillingCycle::class)],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ];
    }
}
