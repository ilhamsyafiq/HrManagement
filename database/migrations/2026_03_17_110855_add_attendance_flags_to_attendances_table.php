<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('is_late')->default(false);
            $table->boolean('is_early_leave')->default(false);
            $table->integer('late_minutes')->default(0);
            $table->integer('early_leave_minutes')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['is_late', 'is_early_leave', 'late_minutes', 'early_leave_minutes']);
        });
    }
};
