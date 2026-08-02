<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the employment type discriminator (full_time|part_time) and the
     * intern-specific profile fields. Part-timers are task-based/flexible:
     * no late/early tracking and no AL/MC/Emergency entitlement.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employment_type')->default('full_time')->after('shift_id');

            // Intern profile fields (nullable; only relevant when is_intern).
            $table->string('university')->nullable()->after('internship_end_date');
            $table->string('academic_supervisor_name')->nullable()->after('university');
            $table->string('academic_supervisor_contact')->nullable()->after('academic_supervisor_name');
            $table->string('course')->nullable()->after('academic_supervisor_contact');
            $table->unsignedInteger('internship_weeks')->nullable()->after('course');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'employment_type',
                'university',
                'academic_supervisor_name',
                'academic_supervisor_contact',
                'course',
                'internship_weeks',
            ]);
        });
    }
};
