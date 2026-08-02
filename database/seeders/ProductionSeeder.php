<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Production seed: the 5 roles + a SINGLE Super Admin account, and nothing else.
 * Idempotent (updateOrCreate), so it is safe to run on every deploy/boot. Change
 * the Super Admin password immediately after the first login (/panel/profile).
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // Roles (idempotent).
        $this->call(RoleSeeder::class);

        $superAdminRoleId = Role::where('name', 'Super Admin')->value('id');

        User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => 'password', // auto-hashed via the model cast
                'role_id' => $superAdminRoleId,
                'is_intern' => false,
                'employment_type' => 'full_time',
            ]
        );
    }
}
