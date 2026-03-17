<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            $table->timestamp('break_in');
            $table->decimal('break_in_lat', 10, 8)->nullable();
            $table->decimal('break_in_lng', 11, 8)->nullable();
            $table->text('break_in_address')->nullable();
            $table->timestamp('break_out')->nullable();
            $table->decimal('break_out_lat', 10, 8)->nullable();
            $table->decimal('break_out_lng', 11, 8)->nullable();
            $table->text('break_out_address')->nullable();
            $table->decimal('duration_minutes', 6, 2)->nullable();
            $table->boolean('is_manually_edited')->default(false);
            $table->foreignId('edited_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('edit_reason')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('breaks');
    }
};
