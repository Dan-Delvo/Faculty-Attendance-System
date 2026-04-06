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
        Schema::create('holidays', function (Blueprint $table) {
            // Define columns
            $table->id();
            $table->date('holiday_date');
            $table->string('name');
            $table->string('type'); // e.g., 'national', 'local', 'observance'
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Unique key
            $table->unique('holiday_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
