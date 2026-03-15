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
            $table->text('undertime_justification')->nullable()->after('undertime_minutes');
            $table->string('undertime_status', 20)->nullable()->after('undertime_justification')->comment('pending, approved, rejected');
            $table->unsignedBigInteger('undertime_reviewed_by')->nullable()->after('undertime_status');
            $table->timestamp('undertime_reviewed_at')->nullable()->after('undertime_reviewed_by');
            $table->text('undertime_review_remarks')->nullable()->after('undertime_reviewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn([
                'undertime_justification',
                'undertime_status',
                'undertime_reviewed_by',
                'undertime_reviewed_at',
                'undertime_review_remarks'
            ]);
        });
    }
};
