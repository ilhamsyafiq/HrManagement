<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $adminRole = Role::where('name', 'Admin')->first();
        $supervisorRole = Role::where('name', 'Supervisor')->first();
        $employeeRole = Role::where('name', 'Employee')->first();

        $hrDept = Department::where('name', 'HR')->first();
        $itDept = Department::where('name', 'IT')->first();

        // Super Admin
        User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'role_id' => $superAdminRole->id,
                'department_id' => $hrDept->id,
            ]
        );

        // Admin
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role_id' => $adminRole->id,
                'department_id' => $hrDept->id,
            ]
        );

        // Supervisor
        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@example.com'],
            [
                'name' => 'Supervisor User',
                'password' => bcrypt('password'),
                'role_id' => $supervisorRole->id,
                'department_id' => $itDept->id,
            ]
        );

        // Employee
        User::firstOrCreate(
            ['email' => 'employee@example.com'],
            [
                'name' => 'Employee User',
                'password' => bcrypt('password'),
                'role_id' => $employeeRole->id,
                'department_id' => $itDept->id,
            ]
        );

        // Intern
        $internRole = Role::where('name', 'Intern')->first();
        User::firstOrCreate(
            ['email' => 'intern@example.com'],
            [
                'name' => 'Intern User',
                'password' => bcrypt('password'),
                'role_id' => $internRole ? $internRole->id : $employeeRole->id,
                'department_id' => $itDept->id,
                'is_intern' => true,
                'supervisor_id' => $supervisor->id,
                'internship_start_date' => now()->subMonths(1),
                'internship_end_date' => now()->addMonths(5),
            ]
        );
    }
}
