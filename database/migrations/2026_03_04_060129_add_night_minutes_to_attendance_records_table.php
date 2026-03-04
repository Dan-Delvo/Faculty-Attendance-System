<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            // Minutes worked during the night differential window (e.g. 10 PM – 6 AM)
            $table->integer('night_minutes')->default(0)->after('overtime_minutes');
            // Overtime minutes that fell within the night differential window
            $table->integer('overtime_night_minutes')->default(0)->after('night_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn(['night_minutes', 'overtime_night_minutes']);
        });
    }
};
