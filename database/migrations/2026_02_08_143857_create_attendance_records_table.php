<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            // Define columns
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('schedule_detail_id')->nullable();
            $table->unsignedBigInteger('internal_schedule_id')->nullable();
            $table->date('attendance_date');
            $table->string('day_of_week');
            $table->datetime('official_time_in');
            $table->datetime('official_time_out');
            $table->string('operational_day_of_week')->nullable();
            $table->datetime('operational_time_in')->nullable();
            $table->datetime('operational_time_out')->nullable();
            $table->datetime('actual_time_in')->nullable();
            $table->datetime('actual_time_out')->nullable();
            $table->integer('late_minutes');
            $table->integer('undertime_minutes');
            $table->integer('overtime_minutes');
            $table->decimal('total_hours_rendered', 5, 2);
            $table->decimal('required_hours', 5, 2);
            $table->string('status');
            $table->text('remarks');
            $table->boolean('is_manual_entry');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Unique Key
            $table->unique(['faculty_id', 'attendance_date', 'schedule_detail_id'], 'unique_attendance_record');

            // Foreign Keys
            $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('cascade');
            $table->foreign('schedule_detail_id')->references('id')->on('schedule_details')->onDelete('set null');
            $table->foreign('internal_schedule_id')->references('id')->on('internal_schedules')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
