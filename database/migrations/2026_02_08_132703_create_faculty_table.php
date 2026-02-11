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
        Schema::create('faculties', function (Blueprint $table) {
            // Define columns
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('department_id')->unsigned();
            $table->string('faculty_code');
            $table->string('biometric_id');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('employment_type')->nullable();
            $table->date('date_hired')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Define unique keys
            $table->unique('user_id', 'unique_faculty_user');
            $table->unique('faculty_code', 'unique_faculty_code');
            $table->unique('biometric_id', 'unique_biometric_id');

            // Define foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('restrict');
            $table->foreign('biometric_id')->references('biometric_id')->on('biometric_logs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculties');
    }
};
