<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_attendance', function (Blueprint $table) {
            $table->id();

            $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
            $table->foreignId('schedule_detail_id')->nullable()->constrained('schedule_details')->nullOnDelete();

            $table->enum('class_type', ['synchronous', 'asynchronous']);
            $table->date('attendance_date');
            $table->time('time_in');
            $table->time('time_out');

            $table->string('screenshot_in');   // storage path for proof of time-in
            $table->string('screenshot_out');  // storage path for proof of time-out

            $table->text('remarks')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['faculty_id', 'status']);
            $table->index(['faculty_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_attendance');
    }
};
