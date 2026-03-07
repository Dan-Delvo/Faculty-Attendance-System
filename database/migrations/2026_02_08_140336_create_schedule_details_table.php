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
            $table->string('day_of_week');
            $table->timestamp('time_in');
            $table->timestamp('time_out')->nullable();
            $table->string('subject_code')->nullable();
            $table->string('subject_desc')->nullable();
            $table->string('room')->nullable();
            $table->integer('hours_required');
            $table->timestamps();
            $table->softDeletes();

            // Unique key
            $table->unique(['schedule_id', 'day_of_week', 'time_in', 'time_out'], 'unique_schedule_detail');

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
