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
        Schema::create('biometric_logs', function (Blueprint $table) {
            // Define columns
            $table->id();
            $table->string('biometric_id');
            $table->dateTime('log_datetime');
            $table->string('log_type');
            $table->string('device_id')->nullable();
            $table->unsignedBigInteger('import_batch_id')->nullable();
            $table->boolean('is_processed')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // unique key
            $table->unique(['biometric_id', 'log_datetime', 'log_type'], 'unique_biometric_log');

            // Define foreign keys
            $table->foreign('biometric_id')->references('biometric_id')->on('faculties')->onDelete('cascade');
            $table->foreign('import_batch_id')->references('id')->on('import_batches')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_logs');
    }
};
