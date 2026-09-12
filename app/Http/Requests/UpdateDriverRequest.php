<?php

namespace App\Http\Requests;

use App\Enums\ActiveStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateDriverRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('driver')) ?? false;
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
            'document_id' => ['nullable', 'string', 'max:30'],
            'license_class' => ['nullable', 'string', 'max:20'],
            'license_expiry' => ['nullable', 'date'],
            'hire_date' => ['nullable', 'date'],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'status' => ['required', new Enum(ActiveStatus::class)],
        ];
    }
}
