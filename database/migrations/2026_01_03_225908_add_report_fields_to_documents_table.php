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
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('type', ['AL', 'MC', 'Attendance Edit', 'Internship Report'])->change();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['draft', 'pending', 'signed', 'revised', 'rejected'])->default('draft');
            $table->text('comments')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->string('signed_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            //
        });
    }
};
