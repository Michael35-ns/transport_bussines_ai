<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Models\TollRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTollRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', TollRecord::class) ?? false;
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
     * so the Form Request stays the single source of truth.
     *
     * @return array<string, mixed>
     */
    public static function buildRules(): array
    {
        return [
            'truck_id' => ['required', 'integer', 'exists:trucks,id'],
            'trip_id' => ['nullable', 'integer', 'exists:trips,id'],
            'occurred_at' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            // Cash only today — no electronic tag (owner answer).
            'payment_method' => ['required', new Enum(PaymentMethod::class)],
        ];
    }
}
