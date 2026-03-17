<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('is_wfh')->default(false)->after('clock_out_address');
            $table->decimal('clock_in_accuracy', 8, 2)->nullable()->after('clock_in_address');
            $table->decimal('clock_out_accuracy', 8, 2)->nullable()->after('clock_out_address');
            $table->decimal('clock_in_distance', 10, 2)->nullable()->after('clock_in_accuracy');
            $table->decimal('clock_out_distance', 10, 2)->nullable()->after('clock_out_accuracy');
            $table->boolean('clock_in_is_mock')->default(false)->after('clock_in_distance');
            $table->boolean('clock_out_is_mock')->default(false)->after('clock_out_distance');
            $table->boolean('location_flagged')->default(false)->after('is_wfh');
            $table->string('location_flag_reason')->nullable()->after('location_flagged');
        });

        // Create office_locations table for admin-managed offices
        Schema::create('office_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('radius')->default(200); // meters
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'is_wfh', 'clock_in_accuracy', 'clock_out_accuracy',
                'clock_in_distance', 'clock_out_distance',
                'clock_in_is_mock', 'clock_out_is_mock',
                'location_flagged', 'location_flag_reason',
            ]);
        });

        Schema::dropIfExists('office_locations');
    }
};
