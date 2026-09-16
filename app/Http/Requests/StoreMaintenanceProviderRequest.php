<?php

namespace App\Http\Requests;

use App\Models\MaintenanceProvider;
use Illuminate\Foundation\Http\FormRequest;

class StoreMaintenanceProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', MaintenanceProvider::class) ?? false;
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
            'contact' => ['nullable', 'string', 'max:120'],
            'specialty' => ['nullable', 'string', 'max:60'],
        ];
    }
}
