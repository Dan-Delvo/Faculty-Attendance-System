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
        Schema::create('schedule_details', function (Blueprint $table) {
            // Columns
            $table->id();
            $table->bigInteger('schedule_id')->unsigned();
            $table->string('day');
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->string('subject_desc')->nullable();
            $table->string('course_code')->nullable();
            $table->string('room_code')->nullable();
            $table->integer('hours_required');
            $table->timestamps();
            $table->softDeletes();

            // Unique key
            $table->unique(['schedule_id', 'day', 'start_time', 'end_time'], 'unique_schedule_detail');

            // Foreign key constraint
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_details');
    }
};
