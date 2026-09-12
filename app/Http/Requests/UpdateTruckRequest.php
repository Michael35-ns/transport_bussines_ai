<?php

namespace App\Http\Requests;

use App\Models\Truck;
use Illuminate\Foundation\Http\FormRequest;

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

        return StoreTruckRequest::buildRules(ignoring: $truck);
    }
}
