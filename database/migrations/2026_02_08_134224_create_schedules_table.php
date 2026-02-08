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
        Schema::create('schedules', function (Blueprint $table) {
            // Define columns
            $table->id();
            $table->bigInteger('faculty_id')->unsigned();
            $table->string('schedule_code');
            $table->year('academic_year');
            $table->integer('semester');
            $table->dateTime('effective_from');
            $table->dateTime('effective_until');
            $table->enum('status', ['draft','active','archived'])->default('active');
            $table->enum('schedule_type', ['fixed','flexible'])->default('flexible');
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            // unique key
            $table->unique(['schedule_code'], 'unique_schedule_code');

            // Define foreign key constraint
            $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
