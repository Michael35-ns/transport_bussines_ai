<?php

namespace App\Http\Requests;

use App\Enums\TripStatus;
use App\Models\Trip;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreTripRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Trip::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return self::buildRules($this->input('status'));
    }

    /**
     * Shared rules, reused by the Livewire component's own validate() call
     * so the Form Request stays the single source of truth.
     *
     * @return array<string, mixed>
     */
    public static function buildRules(?string $status = null): array
    {
        return [
            'truck_id' => ['required', 'integer', 'exists:trucks,id'],
            'driver_id' => ['required', 'integer', 'exists:drivers,id'],
            'route_id' => ['required', 'integer', 'exists:routes,id'],
            'rate_agreement_id' => ['nullable', 'integer', 'exists:rate_agreements,id'],
            'planned_start' => ['nullable', 'date'],
            'actual_start' => ['nullable', 'date'],
            'actual_end' => [
                'nullable', 'date', 'after_or_equal:actual_start',
                // Revenue is recognised at trip completion (FinancialCalculator
                // reads `status = completed` trips by `actual_end`) — a
                // completed trip with no actual_end could never be counted.
                Rule::requiredIf(fn (): bool => $status === TripStatus::Completed->value),
            ],
            // Required: defaults from route.standard_km, but always
            // authoritative once set (docs/decisions/0002).
            'distance' => ['required', 'numeric', 'min:0.01'],
            'distance_estimated' => ['nullable', 'boolean'],
            // Required: defaults from the selected rate agreement, but
            // trips.price stays authoritative (docs/database/conceptual-model.md).
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', new Enum(TripStatus::class)],
        ];
    }
}
