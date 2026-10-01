<?php

namespace Tests\Feature\Livewire\Users;

use App\Livewire\Users\Index;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('users.index'))->assertRedirect(route('login'));
    }

    public function test_a_viewer_can_see_the_user_list(): void
    {
        $this->actingAs(User::factory()->viewer()->create(['name' => 'Alguien']));

        Livewire::test(Index::class)->assertSee('Alguien');
    }

    public function test_an_admin_can_create_a_user_with_a_verified_email(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('name', 'Secretaria')
            ->set('email', 'secretaria@example.com')
            ->set('role', 'admin')
            ->set('password', 'a-strong-password-1')
            ->set('password_confirmation', 'a-strong-password-1')
            ->call('save')
            ->assertHasNoErrors();

        $user = User::where('email', 'secretaria@example.com')->firstOrFail();

        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('a-strong-password-1', $user->password));
    }

    public function test_creating_a_user_requires_a_password(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->set('name', 'Secretaria')
            ->set('email', 'secretaria@example.com')
            ->set('role', 'admin')
            ->call('save')
            ->assertHasErrors(['password']);
    }

    public function test_a_viewer_cannot_create_a_user(): void
    {
        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->set('name', 'Secretaria')
            ->set('email', 'secretaria@example.com')
            ->set('role', 'admin')
            ->set('password', 'a-strong-password-1')
            ->set('password_confirmation', 'a-strong-password-1')
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'secretaria@example.com']);
    }

    public function test_an_admin_can_edit_a_user_without_providing_a_password(): void
    {
        $user = User::factory()->viewer()->create(['name' => 'Old Name']);
        $originalPassword = $user->password;

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('edit', $user)
            ->assertSet('name', 'Old Name')
            ->set('name', 'New Name')
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame($originalPassword, $user->password);
    }

    public function test_an_admin_can_change_a_users_password(): void
    {
        $user = User::factory()->viewer()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)
            ->call('edit', $user)
            ->set('password', 'a-new-password-1')
            ->set('password_confirmation', 'a-new-password-1')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('a-new-password-1', $user->fresh()->password));
    }

    public function test_an_admin_can_delete_another_users_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)->call('delete', $user);

        $this->assertModelMissing($user);
    }

    public function test_an_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        Livewire::test(Index::class)
            ->call('delete', $admin)
            ->assertForbidden();

        $this->assertModelExists($admin);
    }

    public function test_deleting_a_user_with_related_records_shows_a_friendly_error_instead_of_failing(): void
    {
        $creator = User::factory()->create();
        Trip::factory()->create(['created_by' => $creator->id]);

        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Index::class)->call('delete', $creator);

        $this->assertModelExists($creator);
    }

    public function test_a_viewer_cannot_delete_a_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        Livewire::test(Index::class)
            ->call('delete', $user)
            ->assertForbidden();

        $this->assertModelExists($user);
    }
}
