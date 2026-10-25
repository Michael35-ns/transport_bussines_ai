<?php

namespace App\Http\Requests;

use App\Enums\CostTypeScope;
use App\Models\CostType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCostTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', CostType::class) ?? false;
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
     * Shared rules, reused by the Livewire component. `$ignoring` excludes
     * the category being edited from the unique-name check.
     *
     * @return array<string, mixed>
     */
    public static function buildRules(?CostType $ignoring = null): array
    {
        return [
            'name' => ['required', 'string', 'max:60', Rule::unique('cost_types', 'name')->ignore($ignoring?->id)],
            'scope' => ['required', Rule::enum(CostTypeScope::class)],
        ];
    }
}
