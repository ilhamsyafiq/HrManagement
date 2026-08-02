<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Flag + note for attendance records that the system auto-closed because the
     * employee forgot to clock out. The clock-out is snapped to the scheduled
     * shift end (see App\Console\Commands\CloseForgottenClockOuts) so wage/hours
     * are not inflated by an open record running to "now".
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('is_auto_clocked_out')->default(false)->after('is_early_leave');
            $table->string('auto_clock_out_note')->nullable()->after('is_auto_clocked_out');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['is_auto_clocked_out', 'auto_clock_out_note']);
        });
    }
};
