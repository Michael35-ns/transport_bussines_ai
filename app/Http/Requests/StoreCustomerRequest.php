<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Customer::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'tax_id' => ['nullable', 'string', 'max:30'],
            // Typically 0 (cash), 8, or 15 days per the owner, but the
            // column doesn't restrict it to those values.
            'credit_days' => ['nullable', 'integer', 'min:0'],
            'contact' => ['nullable', 'string', 'max:120'],
        ];
    }
}
