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
        Schema::table('faculties', function (Blueprint $table) {
            if (! Schema::hasColumn('faculties', 'external_faculty_id')) {
                $table->unsignedBigInteger('external_faculty_id')->nullable()->after('id');
                $table->unique('external_faculty_id', 'faculties_external_faculty_id_unique');
            }

            if (! Schema::hasColumn('faculties', 'suffix_name')) {
                $table->string('suffix_name')->nullable()->after('last_name');
            }

            if (! Schema::hasColumn('faculties', 'faculty_type')) {
                $table->string('faculty_type')->nullable()->after('suffix_name');
            }

            if (! Schema::hasColumn('faculties', 'assigned_units')) {
                $table->unsignedInteger('assigned_units')->nullable()->after('faculty_type');
            }
        });

        Schema::table('schedules', function (Blueprint $table) {
            if (! Schema::hasColumn('schedules', 'external_faculty_id')) {
                $table->unsignedBigInteger('external_faculty_id')->nullable()->after('faculty_id');
                $table->index('external_faculty_id', 'schedules_external_faculty_id_index');
            }
        });

        Schema::table('schedule_details', function (Blueprint $table) {
            if (Schema::hasColumn('schedule_details', 'day_of_week') && ! Schema::hasColumn('schedule_details', 'day')) {
                $table->renameColumn('day_of_week', 'day');
            }

            if (Schema::hasColumn('schedule_details', 'time_in') && ! Schema::hasColumn('schedule_details', 'start_time')) {
                $table->renameColumn('time_in', 'start_time');
            }

            if (Schema::hasColumn('schedule_details', 'time_out') && ! Schema::hasColumn('schedule_details', 'end_time')) {
                $table->renameColumn('time_out', 'end_time');
            }

            if (! Schema::hasColumn('schedule_details', 'program_code')) {
                $table->string('program_code')->nullable()->after('end_time');
            }

            if (! Schema::hasColumn('schedule_details', 'program_title')) {
                $table->text('program_title')->nullable()->after('program_code');
            }

            if (! Schema::hasColumn('schedule_details', 'year_level')) {
                $table->unsignedTinyInteger('year_level')->nullable()->after('program_title');
            }

            if (! Schema::hasColumn('schedule_details', 'section_name')) {
                $table->string('section_name')->nullable()->after('year_level');
            }

            if (! Schema::hasColumn('schedule_details', 'course_title')) {
                $table->string('course_title')->nullable()->after('section_name');
            }

            if (Schema::hasColumn('schedule_details', 'subject_code') && ! Schema::hasColumn('schedule_details', 'course_code')) {
                $table->renameColumn('subject_code', 'course_code');
            } elseif (! Schema::hasColumn('schedule_details', 'course_code')) {
                $table->string('course_code')->nullable()->after('course_title');
            }

            if (Schema::hasColumn('schedule_details', 'room') && ! Schema::hasColumn('schedule_details', 'room_code')) {
                $table->renameColumn('room', 'room_code');
            } elseif (! Schema::hasColumn('schedule_details', 'room_code')) {
                $table->string('room_code')->nullable()->after('course_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_details', function (Blueprint $table) {
            if (Schema::hasColumn('schedule_details', 'room_code')) {
                $table->dropColumn('room_code');
            }

            if (Schema::hasColumn('schedule_details', 'course_code')) {
                $table->dropColumn('course_code');
            }

            if (Schema::hasColumn('schedule_details', 'course_title')) {
                $table->dropColumn('course_title');
            }

            if (Schema::hasColumn('schedule_details', 'section_name')) {
                $table->dropColumn('section_name');
            }

            if (Schema::hasColumn('schedule_details', 'year_level')) {
                $table->dropColumn('year_level');
            }

            if (Schema::hasColumn('schedule_details', 'program_title')) {
                $table->dropColumn('program_title');
            }

            if (Schema::hasColumn('schedule_details', 'program_code')) {
                $table->dropColumn('program_code');
            }
        });

        Schema::table('schedules', function (Blueprint $table) {
            if (Schema::hasColumn('schedules', 'external_faculty_id')) {
                $table->dropIndex('schedules_external_faculty_id_index');
                $table->dropColumn('external_faculty_id');
            }
        });

        Schema::table('faculties', function (Blueprint $table) {
            if (Schema::hasColumn('faculties', 'assigned_units')) {
                $table->dropColumn('assigned_units');
            }

            if (Schema::hasColumn('faculties', 'faculty_type')) {
                $table->dropColumn('faculty_type');
            }

            if (Schema::hasColumn('faculties', 'suffix_name')) {
                $table->dropColumn('suffix_name');
            }

            if (Schema::hasColumn('faculties', 'external_faculty_id')) {
                $table->dropUnique('faculties_external_faculty_id_unique');
                $table->dropColumn('external_faculty_id');
            }
        });
    }
};
