<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';

use App\Models\AttendanceJustification;
use App\Models\Faculty;

// Find Nelson Angeles
$nelson = Faculty::whereRaw('CONCAT(first_name, " ", last_name) = ?', ['Nelson Angeles'])->first();

if ($nelson) {
    echo "Found Nelson Angeles (ID: {$nelson->id})\n\n";
    
    // Get his undertime justifications
    $justifications = AttendanceJustification::where('faculty_id', $nelson->id)
        ->where('type', 'undertime')
        ->with('attendanceRecord')
        ->get();
    
    echo "Total undertime justifications: " . count($justifications) . "\n\n";
    
    foreach ($justifications as $j) {
        echo "Justification ID: {$j->id}\n";
        echo "Attendance Record ID: {$j->attendance_record_id}\n";
        if ($j->attendanceRecord) {
            echo "Attendance Record Found!\n";
            echo "Undertime Minutes: {$j->attendanceRecord->undertime_minutes}\n";
        } else {
            echo "No Attendance Record linked!\n";
        }
        echo "---\n";
    }
} else {
    echo "Nelson Angeles not found\n";
}
