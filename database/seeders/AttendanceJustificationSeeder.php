<?php

namespace Database\Seeders;

use App\Models\AttendanceJustification;
use App\Models\AttendanceRecord;
use Illuminate\Database\Seeder;

class AttendanceJustificationSeeder extends Seeder
{
    /**
     * Creates undertime justifications linked to actual undertime records
     */
    public function run(): void
    {
        // Get all AttendanceRecords with undertime
        $undertimeRecords = AttendanceRecord::where('undertime_minutes', '>', 0)
            ->with('faculty')
            ->orderBy('id')
            ->get();

        // For first 10 records, create pending justifications
        foreach ($undertimeRecords->take(10) as $record) {
            AttendanceJustification::firstOrCreate(
                [
                    'attendance_record_id' => $record->id,
                    'type' => 'undertime',
                ],
                [
                    'faculty_id' => $record->faculty_id,
                    'justification' => 'I had an important meeting that ran longer than expected and could not leave on time. I will make up this time in the coming week.',
                    'status' => 'pending',
                ]
            );
        }

        // For next 5 records, create approved justifications
        foreach ($undertimeRecords->skip(10)->take(5) as $record) {
            AttendanceJustification::firstOrCreate(
                [
                    'attendance_record_id' => $record->id,
                    'type' => 'undertime',
                ],
                [
                    'faculty_id' => $record->faculty_id,
                    'justification' => 'Client call extended beyond schedule. Unavoidable circumstances.',
                    'status' => 'approved',
                    'reviewed_by' => 1,
                    'reviewed_at' => now(),
                    'review_remarks' => 'Approved - documented reason provided.',
                ]
            );
        }
    }
}
