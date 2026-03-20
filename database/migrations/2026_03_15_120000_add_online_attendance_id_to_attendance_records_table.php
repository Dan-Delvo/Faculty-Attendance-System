<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->unsignedBigInteger('online_attendance_id')
                ->nullable()
                ->after('faculty_id');

            $table->index('online_attendance_id', 'attendance_records_online_attendance_id_index');
            $table->foreign('online_attendance_id', 'attendance_records_online_attendance_id_foreign')
                ->references('id')
                ->on('online_attendance')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropForeign('attendance_records_online_attendance_id_foreign');
            $table->dropIndex('attendance_records_online_attendance_id_index');
            $table->dropColumn('online_attendance_id');
        });
    }
};
