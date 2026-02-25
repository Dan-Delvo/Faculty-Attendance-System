<?php

namespace App\Services;

use App\Models\Faculty;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Models\BiometricLog;
use Carbon\Carbon;

class AttendanceReconciliationService
{
    /**
     * Retrieve and calculate the attendance match for a specific date on the fly.
     * This can be used by both the Faculty Dashboard and the Admin Dashboard.
     *
     * @param Faculty $faculty
     * @param string $date (Y-m-d format)
     * @return array
     */
    public function getDailyAttendanceStatus(Faculty $faculty, string $date): array
    {
        $targetDate = Carbon::parse($date);
        $dayOfWeek = $targetDate->format('l'); // e.g., "Monday"
        $gracePeriodMinutes = 5;

        // 1. RETRIEVE ACTIVE SCHEDULE FOR THIS DAY
        $activeSchedules = $faculty->schedules()
            ->where('status', 'active')
            ->where('effective_from', '<=', $targetDate)
            ->where('effective_until', '>=', $targetDate)
            ->pluck('id');

        $scheduleDetails = [];
        if ($activeSchedules->isNotEmpty()) {
            $scheduleDetails = ScheduleDetail::whereIn('schedule_id', $activeSchedules)
                ->where('day_of_week', $dayOfWeek)
                ->orderBy('time_in')
                ->get();
        }

        // 2. RETRIEVE BIOMETRIC LOGS FOR THAT DAY
        $logs = BiometricLog::where('biometric_id', $faculty->biometric_id)
            ->whereDate('log_datetime', $targetDate->toDateString())
            ->orderBy('log_datetime', 'asc')
            ->get();

        // If no schedule for today...
        if (count($scheduleDetails) === 0) {
            return [
                'status' => 'No Schedule',
                'expected_time_in' => null,
                'expected_time_out' => null,
                'actual_time_in' => $logs->where('log_type', 'IN')->first()?->log_datetime?->format('h:i A'),
                'actual_time_out' => $logs->where('log_type', 'OUT')->last()?->log_datetime?->format('h:i A'),
                'total_hours' => '0h 0m',
                'late_minutes' => 0,
                'undertime_minutes' => 0,
                'raw_logs' => $logs->toArray()
            ];
        }

        // 3. CALCULATION MECHANISM

        // Find earliest expected IN and latest expected OUT for the day
        $firstClass = $scheduleDetails->first();
        $lastClass = $scheduleDetails->last();

        $expectedTimeIn = Carbon::parse($targetDate->toDateString() . ' ' . Carbon::parse($firstClass->time_in)->format('H:i:s'));
        $expectedTimeOut = Carbon::parse($targetDate->toDateString() . ' ' . Carbon::parse($lastClass->time_out)->format('H:i:s'));

        // Find earliest actual IN and latest actual OUT from logs
        $actualInLog = $logs->where('log_type', 'IN')->first();
        $actualOutLog = $logs->where('log_type', 'OUT')->last();

        $actualTimeIn = $actualInLog ? Carbon::parse($actualInLog->log_datetime) : null;
        $actualTimeOut = $actualOutLog ? Carbon::parse($actualOutLog->log_datetime) : null;

        $lateMinutes = 0;
        $undertimeMinutes = 0;
        $totalHours = '0h 0m';
        $status = 'Absent';

        if ($actualTimeIn) {
            $status = 'Present';
            // Calculate Late Minutes (with Grace Period)
            if ($actualTimeIn->greaterThan($expectedTimeIn->copy()->addMinutes($gracePeriodMinutes))) {
                $status = 'Late';
                $lateMinutes = $expectedTimeIn->diffInMinutes($actualTimeIn);
            }
        }

        if ($actualTimeOut && $actualTimeIn) {
            // Calculate Undertime Minutes (Early Out)
            if ($actualTimeOut->lessThan($expectedTimeOut)) {
                $status = ($status === 'Late') ? 'Late & Early-Out' : 'Early-Out';
                $undertimeMinutes = $actualTimeOut->diffInMinutes($expectedTimeOut);
            }

            // Calculate Total Rendered Hours (capped by expected shift)
            $validStart = $actualTimeIn->greaterThan($expectedTimeIn) ? $actualTimeIn : $expectedTimeIn;
            $validEnd = $actualTimeOut->lessThan($expectedTimeOut) ? $actualTimeOut : $expectedTimeOut;

            if ($validEnd->greaterThan($validStart)) {
                $totalDiffInMinutes = $validStart->diffInMinutes($validEnd);
                $hours = floor($totalDiffInMinutes / 60);
                $minutes = $totalDiffInMinutes % 60;

                $totalHours = '';
                if ($hours > 0) {
                    $totalHours .= $hours . 'h ';
                }
                $totalHours .= $minutes . 'm';
            }
        }

        // If faculty only has an IN but no OUT, they are just 'Missing Check-Out'
        if ($actualTimeIn && !$actualTimeOut) {
            $status = 'Missing Check-Out';
        }

        return [
            'status' => $status,
            'expected_time_in' => $expectedTimeIn->format('h:i A'),
            'expected_time_out' => $expectedTimeOut->format('h:i A'),
            'actual_time_in' => $actualTimeIn ? $actualTimeIn->format('h:i A') : '--:--',
            'actual_time_out' => $actualTimeOut ? $actualTimeOut->format('h:i A') : '--:--',
            'total_hours' => $totalHours,
            'late_minutes' => $lateMinutes,
            'undertime_minutes' => $undertimeMinutes,
            'raw_logs' => $logs->toArray()
        ];
    }
}
