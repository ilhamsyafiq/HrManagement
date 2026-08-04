<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Flexible shifts have no fixed start/end time, and a claim item's
     * description is optional — so these columns must allow NULL (they were
     * NOT NULL, which surfaced as "required" errors on save).
     */
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->time('start_time')->nullable()->change();
            $table->time('end_time')->nullable()->change();
        });

        Schema::table('claim_items', function (Blueprint $table) {
            $table->string('description', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->time('start_time')->nullable(false)->change();
            $table->time('end_time')->nullable(false)->change();
        });

        Schema::table('claim_items', function (Blueprint $table) {
            $table->string('description', 255)->nullable(false)->change();
        });
    }
};
