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
        // Add indexes to online_attendance table for faster filtering
        Schema::table('online_attendance', function (Blueprint $table) {
            $table->index('status');
            $table->index('faculty_id');
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });

        // Add indexes to faculties table for faster searching
        Schema::table('faculties', function (Blueprint $table) {
            $table->index('first_name');
            $table->index('last_name');
            $table->index(['first_name', 'last_name']);
        });

        // Add index to users table for email search
        Schema::table('users', function (Blueprint $table) {
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('online_attendance', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['faculty_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('faculties', function (Blueprint $table) {
            $table->dropIndex(['first_name']);
            $table->dropIndex(['last_name']);
            $table->dropIndex(['first_name', 'last_name']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
        });
    }
};
