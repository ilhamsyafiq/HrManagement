<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('priority', ['Low', 'Normal', 'High', 'Urgent'])->default('Normal');
            $table->enum('target', ['All', 'Department', 'Role'])->default('All');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('target_role')->nullable();
            $table->date('publish_date');
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
