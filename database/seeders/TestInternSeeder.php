<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestInternSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Uses environment variables if provided:
     * TEST_INTERN_EMAIL, TEST_INTERN_PASSWORD
     */
    public function run()
    {
        $email = env('TEST_INTERN_EMAIL', 'intern@example.com');
        $password = env('TEST_INTERN_PASSWORD', 'TempPass123!');

        // Ensure roles exist
        $internRole = Role::firstOrCreate(['name' => 'Intern']);
        $supervisorRole = Role::firstOrCreate(['name' => 'Supervisor']);

        // Ensure there's at least one supervisor
        $supervisor = User::whereHas('role', function ($q) use ($supervisorRole) {
            $q->where('name', $supervisorRole->name);
        })->first();

        if (! $supervisor) {
            $supervisor = User::create([
                'name' => 'Test Supervisor',
                'email' => 'supervisor@example.com',
                'password' => Hash::make('SupervisorPass123!'),
                'role_id' => $supervisorRole->id,
            ]);
            $this->command->info('Created test supervisor: supervisor@example.com / SupervisorPass123!');
        }

        // Create or update intern
        $intern = User::where('email', $email)->first();

        $data = [
            'name' => 'Test Intern',
            'email' => $email,
            'password' => Hash::make($password),
            'role_id' => $internRole->id,
            'supervisor_id' => $supervisor->id,
            'is_intern' => true,
            'internship_start_date' => Carbon::now()->format('Y-m-d'),
            'internship_end_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
        ];

        if ($intern) {
            $intern->update($data);
            $this->command->info("Updated intern: {$email} (password: {$password})");
        } else {
            User::create($data);
            $this->command->info("Created intern: {$email} (password: {$password})");
        }
    }
}
