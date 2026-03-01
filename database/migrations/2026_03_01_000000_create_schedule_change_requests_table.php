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
        Schema::create('schedule_change_requests', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('faculty_id')->unsigned();
            $table->bigInteger('schedule_detail_id')->unsigned();

            // What the faculty wants to change to
            $table->string('requested_day_of_week');
            $table->time('requested_time_in');
            $table->time('requested_time_out');
            $table->string('requested_room')->nullable();

            // Effective date for the change
            $table->date('effective_date');

            $table->text('reason');

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // Admin review fields
            $table->bigInteger('reviewed_by')->unsigned()->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('review_remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('cascade');
            $table->foreign('schedule_detail_id')->references('id')->on('schedule_details')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_change_requests');
    }
};
