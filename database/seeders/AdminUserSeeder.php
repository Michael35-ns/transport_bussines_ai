<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * The owner's real account — the one login guaranteed to exist right after
 * a fresh deploy, so there's never a chicken-and-egg problem getting into a
 * brand-new environment.
 *
 * Reads ADMIN_EMAIL/ADMIN_PASSWORD from the environment rather than
 * hard-coding them, since this is a real production credential and must
 * never live in source control (see .env.example for the variables this
 * expects).
 * Set them in this environment's real secrets before running
 * `php artisan db:seed` (or `--class=AdminUserSeeder`).
 *
 * Idempotent: re-running updates the name/password/role on the same email
 * rather than creating a duplicate account, so it's safe to include in every
 * deploy's seed step.
 */
class AdminUserSeeder extends Seeder
{
    /**
     * Seed the admin user.
     */
    public function run(): void
    {
        $email = config('app.admin_email');
        $password = config('app.admin_password');

        if (! $email || ! $password) {
            $this->command->warn('ADMIN_EMAIL / ADMIN_PASSWORD are not set — skipping AdminUserSeeder.');

            return;
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Cristopher Enrique',
                'password' => $password,
                'role' => UserRole::OwnerAdmin,
                'email_verified_at' => now(),
            ]
        );
    }
}
