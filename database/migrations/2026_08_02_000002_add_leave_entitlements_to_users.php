<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add per-employee leave entitlement overrides. Null means "use the
     * company default from config/hr.php". Resolved in LeaveBalanceService.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('al_entitlement')->nullable()->after('internship_end_date');
            $table->unsignedInteger('mc_entitlement')->nullable()->after('al_entitlement');
            $table->unsignedInteger('emergency_entitlement')->nullable()->after('mc_entitlement');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['al_entitlement', 'mc_entitlement', 'emergency_entitlement']);
        });
    }
};
