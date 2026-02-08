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
        Schema::create('internal_schedules', function (Blueprint $table) {
            // Define columns
            $table->id();
            $table->bigInteger('schedule_id')->unsigned();
            $table->bigInteger('faculty_id')->unsigned();
            $table->string('day_of_week');
            $table->timestamp('device_time_in');
            $table->timestamp('device_time_out')->nullable();
            $table->boolean('is_operational')->default(true);
            $table->decimal('required_hours', 5, 2)->default(0);
            $table->string('sync_status');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
            $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_schedules');
    }
};
