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
        Schema::table('online_attendance', function (Blueprint $table) {
            $table->unsignedBigInteger('internal_schedule_id')->nullable()->after('schedule_detail_id');
            $table->foreign('internal_schedule_id')->references('id')->on('internal_schedules')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('online_attendance', function (Blueprint $table) {
            $table->dropForeign(['internal_schedule_id']);
            $table->dropColumn('internal_schedule_id');
        });
    }
};
