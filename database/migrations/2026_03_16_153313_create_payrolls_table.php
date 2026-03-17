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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('month'); // e.g. '2026-03'
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->decimal('total_allowances', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);
            $table->decimal('gross_salary', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2)->default(0);
            $table->decimal('epf_employee', 10, 2)->default(0);
            $table->decimal('epf_employer', 10, 2)->default(0);
            $table->decimal('socso_employee', 10, 2)->default(0);
            $table->decimal('socso_employer', 10, 2)->default(0);
            $table->decimal('eis_employee', 10, 2)->default(0);
            $table->decimal('eis_employer', 10, 2)->default(0);
            $table->decimal('pcb_tax', 10, 2)->default(0);
            $table->enum('status', ['Draft', 'Approved', 'Paid'])->default('Draft');
            $table->date('payment_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
