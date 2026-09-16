<?php

namespace App\Http\Requests;

use App\Models\DriverWorklog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDriverWorklogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', DriverWorklog::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return self::buildRules(null, $this->input('driver_id'));
    }

    /**
     * Shared rules, reused by the Livewire component's own validate() call
     * so the Form Request stays the single source of truth.
     * hourly_rate_snapshot and computed_pay are deliberately absent — both
     * are always computed server-side from the driver's current
     * hourly_rate, never entered directly (docs/decisions/0001).
     *
     * @param  string|int|null  $driverId  Scopes the one-worklog-per-day
     *                                     uniqueness check to this driver.
     * @return array<string, mixed>
     */
    public static function buildRules(?DriverWorklog $ignoring, string|int|null $driverId): array
    {
        return [
            'driver_id' => ['required', 'integer', 'exists:drivers,id'],
            'work_date' => [
                'required',
                'date',
                Rule::unique('driver_worklogs', 'work_date')
                    ->where(fn ($query) => $query->where('driver_id', $driverId))
                    ->ignore($ignoring),
            ],
            'hours' => ['required', 'numeric', 'min:0.01', 'max:24'],
            'notes' => ['nullable', 'string', 'max:200'],
        ];
    }
}
