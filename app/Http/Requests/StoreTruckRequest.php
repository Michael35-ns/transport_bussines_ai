<?php

namespace App\Http\Requests;

use App\Enums\AcquisitionMode;
use App\Enums\ActiveStatus;
use App\Enums\VehicleType;
use App\Models\Truck;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreTruckRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Truck::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return self::buildRules(includeOdometer: true);
    }

    /**
     * The shared field rules for a truck — also used by UpdateTruckRequest
     * and the Trucks Livewire component, so this is the single source of
     * truth for what a valid truck looks like.
     *
     * @return array<string, mixed>
     */
    public static function buildRules(?Truck $ignoring = null, bool $includeOdometer = false): array
    {
        $rules = [
            'plate' => ['required', 'string', 'max:20', Rule::unique('trucks', 'plate')->ignore($ignoring)],
            'internal_no' => ['nullable', 'string', 'max:20', Rule::unique('trucks', 'internal_no')->ignore($ignoring)],
            'vehicle_type' => ['required', new Enum(VehicleType::class)],
            'make' => ['nullable', 'string', 'max:60'],
            'model' => ['nullable', 'string', 'max:60'],
            'year' => ['nullable', 'integer', 'min:1980', 'max:'.(date('Y') + 1)],
            'acquisition_date' => ['nullable', 'date'],
            'acquisition_mode' => ['required', new Enum(AcquisitionMode::class)],
            'financing_monthly' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', new Enum(ActiveStatus::class)],
            'base_yard' => ['nullable', 'string', 'max:120'],
        ];

        if ($includeOdometer) {
            // A one-time starting baseline only. Afterward the estimate is
            // system-maintained and re-anchored by service records, not
            // hand-edited (docs/decisions/0002-trip-distance-source.md).
            $rules['current_odometer'] = ['nullable', 'numeric', 'min:0'];
        }

        return $rules;
    }
}
