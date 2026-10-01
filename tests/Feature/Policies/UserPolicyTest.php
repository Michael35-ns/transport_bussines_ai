<?php

namespace Tests\Feature\Policies;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * UserPolicy's one addition over the shared ModelPolicy rule: nobody can
 * delete their own account, so an admin can never lock themselves out.
 * Everything else (read-all, non-viewer-writes) is covered by
 * ModelPolicyTest's shared loop.
 */
class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertFalse($admin->can('delete', $admin));
    }

    public function test_an_admin_can_delete_a_different_users_account(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->create();

        $this->assertTrue($admin->can('delete', $other));
    }

    public function test_an_owner_admin_cannot_delete_their_own_account(): void
    {
        $owner = User::factory()->ownerAdmin()->create();

        $this->assertFalse($owner->can('delete', $owner));
    }
}
