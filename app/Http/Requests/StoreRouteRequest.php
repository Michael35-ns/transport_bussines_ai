<?php

namespace App\Http\Requests;

use App\Models\Route;
use Illuminate\Foundation\Http\FormRequest;

class StoreRouteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Route::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'origin' => ['required', 'string', 'max:120'],
            'destination' => ['required', 'string', 'max:120'],
            // Required: the sole trip-distance source while no odometer is
            // captured (docs/decisions/0002-trip-distance-source.md). A trip
            // against a route with no standard_km can't produce a per-km KPI.
            'standard_km' => ['required', 'numeric', 'min:0.01'],
            'typical_toll_cost' => ['nullable', 'numeric', 'min:0'],
            'is_round_trip' => ['nullable', 'boolean'],
        ];
    }
}
