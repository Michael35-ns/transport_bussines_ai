<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * StoreUserRequest::buildRules() is shared by UpdateUserRequest and the
 * Livewire component. Password is required on create, optional on edit.
 */
class UserRequestsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'name' => 'Nueva Secretaria',
            'email' => 'secretaria@example.com',
            'role' => 'admin',
            'password' => 'a-strong-password-1',
            'password_confirmation' => 'a-strong-password-1',
        ];
    }

    public function test_accepts_a_valid_payload(): void
    {
        $validator = Validator::make($this->validPayload(), StoreUserRequest::buildRules());

        $this->assertFalse($validator->fails());
    }

    public function test_requires_a_password_on_create(): void
    {
        $data = $this->validPayload();
        unset($data['password'], $data['password_confirmation']);

        $validator = Validator::make($data, StoreUserRequest::buildRules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_password_is_optional_when_editing(): void
    {
        $data = $this->validPayload();
        unset($data['password'], $data['password_confirmation']);

        $validator = Validator::make($data, StoreUserRequest::buildRules(passwordRequired: false));

        $this->assertFalse($validator->fails());
    }

    public function test_rejects_a_duplicate_email(): void
    {
        $existing = User::factory()->create(['email' => 'secretaria@example.com']);

        $validator = Validator::make($this->validPayload(), StoreUserRequest::buildRules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());

        // Editing that same user with their own email is still allowed.
        $validator = Validator::make($this->validPayload(), StoreUserRequest::buildRules(ignoring: $existing, passwordRequired: false));

        $this->assertFalse($validator->fails());
    }

    public function test_rejects_an_invalid_role(): void
    {
        $data = $this->validPayload();
        $data['role'] = 'super_admin';

        $validator = Validator::make($data, StoreUserRequest::buildRules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('role', $validator->errors()->toArray());
    }
}
