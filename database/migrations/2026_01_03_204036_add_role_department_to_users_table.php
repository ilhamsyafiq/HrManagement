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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_intern')->default(false);
            $table->date('internship_start_date')->nullable();
            $table->date('internship_end_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn(['role_id', 'department_id', 'supervisor_id', 'is_intern', 'internship_start_date', 'internship_end_date']);
        });
    }
};
