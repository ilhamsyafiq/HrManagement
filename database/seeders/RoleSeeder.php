<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Role::updateOrCreate(['name' => 'Super Admin'], ['description' => 'Full system control']);
        \App\Models\Role::updateOrCreate(['name' => 'Admin'], ['description' => 'Manage employees and approve requests']);
        // "Supervisor" role retired — supervisor powers now come from having direct
        // reports (users.supervisor_id), not a role. See User::isSupervisor().
        \App\Models\Role::updateOrCreate(['name' => 'Employee'], ['description' => 'Regular employee access']);
        \App\Models\Role::updateOrCreate(['name' => 'Intern'], ['description' => 'Intern employee with limited access']);
    }
}
