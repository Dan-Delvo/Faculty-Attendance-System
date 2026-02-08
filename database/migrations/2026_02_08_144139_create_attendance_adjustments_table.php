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
        Schema::create('attendance_adjustments', function (Blueprint $table) {
            // Define columns
            $table->id();
            $table->unsignedBigInteger('attendance_record_id');
            $table->string('adjustment_type');
            $table->datetime('original_time_in');
            $table->datetime('original_time_out');
            $table->string('original_status');
            $table->datetime('adjusted_time_in')->nullable();
            $table->datetime('adjusted_time_out')->nullable();
            $table->string('adjusted_status')->nullable();
            $table->text('reason');
            $table->unsignedBigInteger('adjusted_by');
            $table->timestamps();
            $table->softDeletes();

            // Define foreign keys
            $table->foreign('attendance_record_id')->references('id')->on('attendance_records')->onDelete('cascade');
            $table->foreign('adjusted_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_adjustments');
    }
};
