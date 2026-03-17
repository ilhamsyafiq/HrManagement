<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Department::firstOrCreate(['name' => 'IT'], ['description' => 'Information Technology']);
        \App\Models\Department::firstOrCreate(['name' => 'HR'], ['description' => 'Human Resources']);
        \App\Models\Department::firstOrCreate(['name' => 'Finance'], ['description' => 'Finance Department']);
        \App\Models\Department::firstOrCreate(['name' => 'Marketing'], ['description' => 'Marketing Department']);
    }
}
