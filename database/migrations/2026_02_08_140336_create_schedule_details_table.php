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
        Schema::create('schedule_details', function (Blueprint $table) {
            // Columns
            $table->id();
            $table->bigInteger('schedule_id')->unsigned();
            $table->string('day_of_week');
            $table->timestamp('timestamp_in');
            $table->timestamp('timestamp_out');
            $table->string('subject_code');
            $table->string('subject_desc');
            $table->string('room');
            $table->integer('hours_required');
            $table->timestamps();
            $table->softDeletes();

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
