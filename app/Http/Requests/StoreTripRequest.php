<?php

namespace App\Http\Requests;

use App\Models\Trip;
use Illuminate\Foundation\Http\FormRequest;

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
        return self::buildRules();
    }

    /**
     * Shared rules, reused by the Livewire component's own validate() call
     * so the Form Request stays the single source of truth. `status` and
     * `actual_end` are deliberately absent — every trip captured through the
     * UI is entered after the fact, so the component always sets
     * `status = completed` and `actual_end = now()` itself rather than
     * asking for them.
     *
     * @return array<string, mixed>
     */
    public static function buildRules(): array
    {
        return [
            'truck_id' => ['required', 'integer', 'exists:trucks,id'],
            'driver_id' => ['required', 'integer', 'exists:drivers,id'],
            'route_id' => ['required', 'integer', 'exists:routes,id'],
            'rate_agreement_id' => ['nullable', 'integer', 'exists:rate_agreements,id'],
            // Required: defaults from route.standard_km, but always
            // authoritative once set (docs/decisions/0002).
            'distance' => ['required', 'numeric', 'min:0.01'],
            'distance_estimated' => ['nullable', 'boolean'],
            // Required: defaults from the selected rate agreement, but
            // trips.price stays authoritative (docs/database/conceptual-model.md).
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
