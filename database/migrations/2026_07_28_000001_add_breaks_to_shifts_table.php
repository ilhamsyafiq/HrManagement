<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add an optional unpaid break window to a shift.
     *
     * This lets a single shift express a split working day such as the Friday
     * (Male) schedule: 09:00-12:30 and 14:30-17:30 is one shift running
     * 09:00-17:30 with a 12:30-14:30 (mosque/prayer) break.
     */
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->time('break_start')->nullable()->after('end_time');
            $table->time('break_end')->nullable()->after('break_start');
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['break_start', 'break_end']);
        });
    }
};
