<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates the initial Super Admin account.
 *
 * Credentials come from env:
 *   SUPER_ADMIN_EMAIL
 *   SUPER_ADMIN_PASSWORD
 *
 * If not set, falls back to a dev-only default that prints a warning.
 * NEVER rely on the fallback in production.
 */
class DefaultAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email    = env('SUPER_ADMIN_EMAIL');
        $password = env('SUPER_ADMIN_PASSWORD');

        if (! $email || ! $password) {
            if (app()->environment('production')) {
                $this->command?->warn('DefaultAdminSeeder skipped in production: SUPER_ADMIN_EMAIL / SUPER_ADMIN_PASSWORD not set.');
                return;
            }

            $email    = 'super@mnjiz.local';
            $password = 'ChangeMeImmediately!';
            $this->command?->warn("DefaultAdminSeeder: using dev fallback credentials ({$email}). CHANGE THEM.");
        }

        Admin::query()->updateOrCreate(
            ['email' => $email],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make($password),
                'role'     => Admin::ROLE_SUPER_ADMIN,
                'status'   => Admin::STATUS_ACTIVE,
            ]
        );
    }
}
