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
        Schema::create('dtr_records', function (Blueprint $table) {
            // table columns
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            // Summary Metrics
            $table->unsignedTinyInteger('total_days_present')->default(0);
            $table->unsignedTinyInteger('total_days_absent')->default(0);
            $table->unsignedTinyInteger('total_days_late')->default(0);
            $table->unsignedInteger('total_late_minutes')->default(0);
            $table->unsignedInteger('total_undertime_minutes')->default(0);
            $table->decimal('total_hours_rendered', 8, 2)->default(0);
            $table->decimal('total_hours_required', 8, 2)->default(0);
            $table->string('status')->default('pending');
            $table->timestamp('finalized_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Unique Key
            $table->unique(['faculty_id', 'month', 'year']);

            // Foreign Keys
            $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dtr_records');
    }
};
