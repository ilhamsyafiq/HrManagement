<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Split shifts + flexible working time.
 *
 * `shift_segments` holds multiple non-contiguous work blocks per shift day
 * (e.g. 08:00-13:00 + 20:00-23:30 = 8h30m). When a shift has segment rows its
 * paid hours are the sum over segments; a shift with NO segments keeps the
 * legacy single-span-minus-break behaviour (fully backward-compatible).
 *
 * `shifts.is_flexible` marks a shift whose employees only need to fulfil total
 * hours: no late clock-in / early-leave tracking.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->boolean('is_flexible')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('is_flexible');
        });

        Schema::dropIfExists('shift_segments');
    }
};
