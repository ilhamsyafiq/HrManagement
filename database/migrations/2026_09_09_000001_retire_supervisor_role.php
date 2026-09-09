<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Retire the dedicated "Supervisor" role. Supervisor powers are now derived from
 * the reporting relationship (users.supervisor_id) via User::isSupervisor(), so
 * the role is redundant. Any user still holding it is moved to "Employee" — they
 * keep supervisor powers automatically if anyone reports to them.
 */
return new class extends Migration
{
    public function up(): void
    {
        $supervisor = DB::table('roles')->where('name', 'Supervisor')->first();
        $employee = DB::table('roles')->where('name', 'Employee')->first();

        if (! $supervisor) {
            return; // already retired
        }

        if ($employee) {
            DB::table('users')
                ->where('role_id', $supervisor->id)
                ->update(['role_id' => $employee->id]);
        }

        DB::table('roles')->where('id', $supervisor->id)->delete();
    }

    public function down(): void
    {
        // Recreate the role so the schema can roll back. User reassignments are
        // not reverted (that information is lost on the way down).
        DB::table('roles')->updateOrInsert(
            ['name' => 'Supervisor'],
            ['description' => 'Manage interns and first approver for leaves']
        );
    }
};
