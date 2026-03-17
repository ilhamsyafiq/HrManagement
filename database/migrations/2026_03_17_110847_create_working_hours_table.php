<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->time('work_start')->default('09:00:00');
            $table->time('work_end')->default('17:30:00');
            $table->time('break_start')->default('13:00:00');
            $table->time('break_end')->default('14:00:00');
            $table->integer('late_threshold_minutes')->default(15);
            $table->integer('early_leave_threshold_minutes')->default(15);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('working_hours');
    }
};
