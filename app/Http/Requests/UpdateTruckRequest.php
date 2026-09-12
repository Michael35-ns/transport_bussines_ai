<?php

namespace App\Http\Requests;

use App\Enums\AcquisitionMode;
use App\Enums\ActiveStatus;
use App\Enums\VehicleType;
use App\Models\Truck;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateTruckRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('truck')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Truck $truck */
        $truck = $this->route('truck');

        return [
            'plate' => ['required', 'string', 'max:20', Rule::unique('trucks', 'plate')->ignore($truck)],
            'internal_no' => ['nullable', 'string', 'max:20', Rule::unique('trucks', 'internal_no')->ignore($truck)],
            'vehicle_type' => ['required', new Enum(VehicleType::class)],
            'make' => ['nullable', 'string', 'max:60'],
            'model' => ['nullable', 'string', 'max:60'],
            'year' => ['nullable', 'integer', 'min:1980', 'max:'.(date('Y') + 1)],
            'acquisition_date' => ['nullable', 'date'],
            'acquisition_mode' => ['required', new Enum(AcquisitionMode::class)],
            'financing_monthly' => ['nullable', 'numeric', 'min:0'],
            // current_odometer is intentionally not editable here — it's a
            // maintained estimate re-anchored by maintenance records, not a
            // field the user hand-edits after the truck exists.
            'status' => ['required', new Enum(ActiveStatus::class)],
            'base_yard' => ['nullable', 'string', 'max:120'],
        ];
    }
}
