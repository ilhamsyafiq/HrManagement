<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-admin dashboard customization: which Filament widgets are shown and in
     * what order. Stored as JSON: { "widgets": [ { "key": "<class>", "visible": bool }, ... ] }.
     * Null = use the default layout.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('dashboard_preferences')->nullable()->after('emergency_entitlement');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('dashboard_preferences');
        });
    }
};
