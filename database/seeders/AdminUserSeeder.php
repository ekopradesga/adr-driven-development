<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates the initial Super Administrator user account.
 *
 * Credentials are read from environment variables:
 *   DEFAULT_ADMIN_NAME     (optional, defaults to 'Super Administrator')
 *   DEFAULT_ADMIN_EMAIL    (required in non-local environments)
 *   DEFAULT_ADMIN_PASSWORD (required in non-local environments)
 *
 * In local environments, development fallback values are used when the
 * environment variables are not set. A console warning is displayed whenever
 * fallback credentials are active.
 *
 * Uses firstOrCreate to be safe on re-runs.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $isLocal      = app()->environment('local');
        $usingFallback = false;

        $name     = env('DEFAULT_ADMIN_NAME', 'Super Administrator');
        $email    = env('DEFAULT_ADMIN_EMAIL');
        $password = env('DEFAULT_ADMIN_PASSWORD');

        if (empty($email)) {
            if (!$isLocal) {
                $this->command->error(
                    'AdminUserSeeder: DEFAULT_ADMIN_EMAIL must be set in .env ' .
                    '(not running in local environment).'
                );
                return;
            }
            $email         = 'admin@isp.local';
            $usingFallback = true;
        }

        if (empty($password)) {
            if (!$isLocal) {
                $this->command->error(
                    'AdminUserSeeder: DEFAULT_ADMIN_PASSWORD must be set in .env ' .
                    '(not running in local environment).'
                );
                return;
            }
            $password      = 'Admin@1234!';
            $usingFallback = true;
        }

        if ($usingFallback) {
            $this->command->warn(
                'AdminUserSeeder: using FALLBACK credentials (local environment only). ' .
                'Set DEFAULT_ADMIN_NAME, DEFAULT_ADMIN_EMAIL, and DEFAULT_ADMIN_PASSWORD ' .
                'in .env before deploying to any other environment.'
            );
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => $name,
                'password' => Hash::make($password),
                'status'   => UserStatus::Active->value,
            ]
        );

        $superAdminRole = Role::where('slug', 'super-admin')->first();

        if ($superAdminRole) {
            $user->roles()->syncWithoutDetaching([$superAdminRole->id]);
        }

        $this->command->info("Admin user ready: {$email}");

        if ($usingFallback) {
            $this->command->warn('⚠  Change the default password immediately after first login.');
        }
    }
}

