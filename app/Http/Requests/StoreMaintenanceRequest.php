<?php

namespace App\Http\Requests;

use App\Enums\MaintenanceType;
use App\Models\Maintenance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreMaintenanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Maintenance::class) ?? false;
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
     * so the Form Request stays the single source of truth. `total` is
     * intentionally absent — it's always computed from the three cost
     * fields, never entered directly.
     *
     * @return array<string, mixed>
     */
    public static function buildRules(): array
    {
        return [
            'truck_id' => ['required', 'integer', 'exists:trucks,id'],
            'type' => ['required', new Enum(MaintenanceType::class)],
            'cost_type_id' => ['nullable', 'integer', 'exists:cost_types,id'],
            'provider_id' => ['required', 'integer', 'exists:maintenance_providers,id'],
            'schedule_id' => ['nullable', 'integer', 'exists:maintenance_schedule,id'],
            'entry_date' => ['required', 'date'],
            'completion_date' => ['nullable', 'date', 'after_or_equal:entry_date'],
            // Re-anchors trucks.current_odometer when present (docs/decisions/0002).
            'odometer' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:255'],
            'parts_cost' => ['required', 'numeric', 'min:0'],
            'labor_cost' => ['required', 'numeric', 'min:0'],
            'other_cost' => ['required', 'numeric', 'min:0'],
            // Counts against availability only when type = corrective.
            'downtime_days' => ['required', 'numeric', 'min:0'],
        ];
    }
}
