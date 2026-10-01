<?php

namespace App\Http\Requests;

use App\Concerns\PasswordValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreUserRequest extends FormRequest
{
    use PasswordValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
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
     * so the Form Request stays the single source of truth. The password is
     * required on create, optional on edit ("leave blank to keep the
     * current one") — never shown or pre-filled either way.
     *
     * @return array<string, mixed>
     */
    public static function buildRules(?User $ignoring = null, bool $passwordRequired = true): array
    {
        // ['required', 'string', Password::default(), 'confirmed'] — swap
        // the leading 'required' for 'nullable' when editing.
        $password = (new self)->passwordRules();
        if (! $passwordRequired) {
            $password[0] = 'nullable';
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ignoring)],
            'role' => ['required', new Enum(UserRole::class)],
            'password' => $password,
        ];
    }
}
