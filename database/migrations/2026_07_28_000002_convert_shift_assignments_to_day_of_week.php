<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Move shift assignments from a specific calendar date to a recurring
     * day of the week (0 = Sunday ... 6 = Saturday). Each employee now has a
     * repeating weekly pattern; a weekday with no row is a rest/off day, so
     * off days can differ per employee.
     */
    public function up(): void
    {
        // The user_id foreign key leans on the (user_id, date) unique index, so
        // give it a standalone index before dropping that unique.
        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->index('user_id', 'sa_user_id_tmp_idx');
        });

        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->dropUnique('shift_assignments_user_id_date_unique');
            $table->dropColumn('date');
        });

        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->unsignedTinyInteger('day_of_week')->after('shift_id');
        });

        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->unique(['user_id', 'day_of_week']);
            $table->dropIndex('sa_user_id_tmp_idx');
        });
    }

    public function down(): void
    {
        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->index('user_id', 'sa_user_id_tmp_idx');
        });

        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->dropUnique('shift_assignments_user_id_day_of_week_unique');
            $table->dropColumn('day_of_week');
        });

        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->date('date')->after('shift_id');
        });

        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->unique(['user_id', 'date']);
            $table->dropIndex('sa_user_id_tmp_idx');
        });
    }
};
