<?php

namespace App\Services;

use App\Models\Faculty;
use App\Models\Holiday;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Models\BiometricLog;
use App\Models\OnlineAttendanceRequest;
use Carbon\Carbon;

class AttendanceReconciliationService
{
    /**
     * Retrieve and calculate the attendance match for a specific date on the fly.
     * This can be used by both the Faculty Dashboard and the Admin Dashboard.
     *
     * Considers both biometric logs AND approved online attendance entries.
     * Approved online attendance takes precedence when no biometric logs exist;
     * when both exist, the earliest IN and latest OUT across both sources are used.
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

        // 0. CHECK IF THE DATE IS A HOLIDAY ─────────────────────────────────
        $isHoliday = Holiday::where(function ($q) use ($targetDate) {
            // Exact non-recurring match
            $q->where('holiday_date', $targetDate->toDateString())
              ->where('is_recurring', false);
        })->orWhere(function ($q) use ($targetDate) {
            // Recurring: same month + day regardless of year
            $q->where('is_recurring', true)
              ->whereMonth('holiday_date', $targetDate->month)
              ->whereDay('holiday_date', $targetDate->day);
        })->exists();

        if ($isHoliday) {
            return [
                'status'             => 'Holiday',
                'expected_time_in'   => null,
                'expected_time_out'  => null,
                'actual_time_in'     => '--:--',
                'actual_time_out'    => '--:--',
                'total_hours'        => '0h 0m',
                'late_minutes'       => 0,
                'undertime_minutes'  => 0,
                'raw_logs'           => [],
                'online_attendance'  => false,
            ];
        }

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

        // 3. RETRIEVE APPROVED ONLINE ATTENDANCE FOR THAT DAY
        $onlineEntries = OnlineAttendanceRequest::where('faculty_id', $faculty->id)
            ->whereDate('attendance_date', $targetDate->toDateString())
            ->where('status', 'approved')
            ->orderBy('time_in', 'asc')
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
                'raw_logs' => $logs->toArray(),
                'online_attendance' => false,
            ];
        }

        // 4. DETERMINE ACTUAL IN/OUT — merge biometric logs + approved online attendance

        $biometricIn  = $logs->where('log_type', 'IN')->first()
            ? Carbon::parse($logs->where('log_type', 'IN')->first()->log_datetime)
            : null;
        $biometricOut = $logs->where('log_type', 'OUT')->last()
            ? Carbon::parse($logs->where('log_type', 'OUT')->last()->log_datetime)
            : null;

        $onlineIn  = $onlineEntries->isNotEmpty()
            ? Carbon::parse($targetDate->toDateString() . ' ' . $onlineEntries->first()->time_in)
            : null;
        $onlineOut = $onlineEntries->isNotEmpty()
            ? Carbon::parse($targetDate->toDateString() . ' ' . $onlineEntries->last()->time_out)
            : null;

        // Pick the earliest IN and latest OUT across both sources
        $actualTimeIn  = $this->earliest($biometricIn, $onlineIn);
        $actualTimeOut = $this->latest($biometricOut, $onlineOut);

        // Track whether online attendance contributed to this day
        $hasOnline = $onlineEntries->isNotEmpty();

        // 5. CALCULATION MECHANISM

        // Find earliest expected IN and latest expected OUT for the day
        $firstClass = $scheduleDetails->first();
        $lastClass = $scheduleDetails->last();

        $expectedTimeIn = Carbon::parse($targetDate->toDateString() . ' ' . Carbon::parse($firstClass->time_in)->format('H:i:s'));
        $expectedTimeOut = Carbon::parse($targetDate->toDateString() . ' ' . Carbon::parse($lastClass->time_out)->format('H:i:s'));

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
            'raw_logs' => $logs->toArray(),
            'online_attendance' => $hasOnline,
        ];
    }

    /**
     * Return the earliest of two Carbon instances (either may be null).
     */
    private function earliest(?Carbon $a, ?Carbon $b): ?Carbon
    {
        if (!$a) return $b;
        if (!$b) return $a;
        return $a->lessThanOrEqualTo($b) ? $a : $b;
    }

    /**
     * Return the latest of two Carbon instances (either may be null).
     */
    private function latest(?Carbon $a, ?Carbon $b): ?Carbon
    {
        if (!$a) return $b;
        if (!$b) return $a;
        return $a->greaterThanOrEqualTo($b) ? $a : $b;
    }
}
