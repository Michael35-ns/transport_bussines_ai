<?php

namespace App\Http\Requests;

use App\Enums\MaintenanceIntervalType;
use App\Models\MaintenanceSchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreMaintenanceScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', MaintenanceSchedule::class) ?? false;
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
     * so the Form Request stays the single source of truth.
     *
     * @return array<string, mixed>
     */
    public static function buildRules(): array
    {
        return [
            'truck_id' => ['required', 'integer', 'exists:trucks,id'],
            'task_name' => ['required', 'string', 'max:80'],
            'interval_type' => ['required', new Enum(MaintenanceIntervalType::class)],
            'interval_value' => ['nullable', 'numeric', 'min:0.01'],
            // The baseline this task was last done at — null means "not yet
            // done", so nextDueOdometer() falls back to the truck's current
            // estimate (MaintenanceSchedule::nextDueOdometer()).
            'last_done_odometer' => ['nullable', 'numeric', 'min:0'],
            'lead_km' => ['required', 'numeric', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
