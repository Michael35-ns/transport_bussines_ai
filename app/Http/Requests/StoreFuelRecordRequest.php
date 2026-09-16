<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Models\FuelRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreFuelRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', FuelRecord::class) ?? false;
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
     * intentionally absent — it's always computed as liters * unit_price.
     *
     * @return array<string, mixed>
     */
    public static function buildRules(): array
    {
        return [
            'truck_id' => ['required', 'integer', 'exists:trucks,id'],
            'trip_id' => ['nullable', 'integer', 'exists:trips,id'],
            'fuel_station_id' => ['required', 'integer', 'exists:fuel_stations,id'],
            'occurred_at' => ['required', 'date'],
            'liters' => ['required', 'numeric', 'min:0.01'],
            'unit_price' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', new Enum(PaymentMethod::class)],
        ];
    }
}
