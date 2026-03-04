<?php

namespace App\Services;

use App\Models\Faculty;
use App\Models\Holiday;
use App\Models\Schedule;
use App\Models\InternalSchedule;
use App\Models\ScheduleDetail;
use App\Models\ScheduleChangeRequest;
use App\Models\BiometricLog;
use App\Models\OnlineAttendanceRequest;
use Carbon\Carbon;

class AttendanceReconciliationService
{
    /**
     * Retrieve and calculate the attendance match for a specific date on the fly.
     *
     * Priority chain for determining expected schedule:
     *   1. Approved ScheduleChangeRequest (overrides the original schedule detail)
     *   2. InternalSchedule (operational / biometric device times)
     *   (Official ScheduleDetail is NOT used — only internal schedule times apply)
     *
     * Actual attendance sources:
     *   - BiometricLog (physical clock-in/out)
     *   - Approved OnlineAttendanceRequest
     *   Earliest IN and latest OUT across both sources are used.
     *
     * @param Faculty $faculty
     * @param string  $date  (Y-m-d format)
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
                'status' => 'Holiday',
                'expected_time_in' => null,
                'expected_time_out' => null,
                'actual_time_in' => '--:--',
                'actual_time_out' => '--:--',
                'total_hours' => '0h 0m',
                'late_minutes' => 0,
                'undertime_minutes' => 0,
                'raw_logs' => [],
                'online_attendance' => false,
            ];
        }
        // ── 1. RESOLVE EXPECTED SCHEDULE FOR THIS DAY ────────────────────

        $activeScheduleIds = $faculty->schedules()
            ->where('status', 'active')
            ->where('effective_from', '<=', $targetDate)
            ->where('effective_until', '>=', $targetDate)
            ->pluck('id');

        // Collect the effective expected entries for today:
        //   { time_in (H:i:s), time_out (H:i:s), source }
        $expectedEntries = $this->resolveExpectedEntries(
            $faculty,
            $activeScheduleIds,
            $dayOfWeek,
            $targetDate
        );

        // ── 2. RETRIEVE BIOMETRIC LOGS FOR THAT DAY ─────────────────────

        $logs = BiometricLog::where('biometric_id', $faculty->biometric_id)
            ->whereDate('log_datetime', $targetDate->toDateString())
            ->orderBy('log_datetime', 'asc')
            ->get();

        // ── 3. RETRIEVE APPROVED ONLINE ATTENDANCE FOR THAT DAY ─────────

        $onlineEntries = OnlineAttendanceRequest::where('faculty_id', $faculty->id)
            ->whereDate('attendance_date', $targetDate->toDateString())
            ->where('status', 'approved')
            ->orderBy('time_in', 'asc')
            ->get();

        // ── No schedule for today ────────────────────────────────────────

        if (empty($expectedEntries)) {
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
                'schedule_source' => null,
            ];
        }

        // ── 4. DETERMINE ACTUAL IN/OUT ───────────────────────────────────

        $biometricIn = $logs->where('log_type', 'IN')->first()
            ? Carbon::parse($logs->where('log_type', 'IN')->first()->log_datetime)
            : null;
        $biometricOut = $logs->where('log_type', 'OUT')->last()
            ? Carbon::parse($logs->where('log_type', 'OUT')->last()->log_datetime)
            : null;

        $onlineIn = $onlineEntries->isNotEmpty()
            ? Carbon::parse($targetDate->toDateString() . ' ' . $onlineEntries->first()->time_in)
            : null;
        $onlineOut = $onlineEntries->isNotEmpty()
            ? Carbon::parse($targetDate->toDateString() . ' ' . $onlineEntries->last()->time_out)
            : null;

        $actualTimeIn = $this->earliest($biometricIn, $onlineIn);
        $actualTimeOut = $this->latest($biometricOut, $onlineOut);
        $hasOnline = $onlineEntries->isNotEmpty();

        // ── 5. EXPECTED WINDOW (earliest in / latest out) ────────────────

        $sortedByIn = collect($expectedEntries)->sortBy('time_in');
        $sortedByOut = collect($expectedEntries)->sortByDesc('time_out');

        $expectedTimeIn = Carbon::parse($targetDate->toDateString() . ' ' . $sortedByIn->first()['time_in']);
        $expectedTimeOut = Carbon::parse($targetDate->toDateString() . ' ' . $sortedByOut->first()['time_out']);

        // Determine dominant schedule source for display
        $scheduleSource = $sortedByIn->first()['source'] ?? 'official';

        // ── 6. CALCULATE STATUS / LATE / UNDERTIME ───────────────────────

        $lateMinutes = 0;
        $undertimeMinutes = 0;
        $totalHours = '0h 0m';
        $status = 'Absent';

        if ($actualTimeIn) {
            $status = 'Present';
            if ($actualTimeIn->greaterThan($expectedTimeIn->copy()->addMinutes($gracePeriodMinutes))) {
                $status = 'Late';
                $lateMinutes = $expectedTimeIn->diffInMinutes($actualTimeIn);
            }
        }

        if ($actualTimeOut && $actualTimeIn) {
            if ($actualTimeOut->lessThan($expectedTimeOut)) {
                $status = ($status === 'Late') ? 'Late & Early-Out' : 'Early-Out';
                $undertimeMinutes = $actualTimeOut->diffInMinutes($expectedTimeOut);
            }

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

        if ($actualTimeIn && !$actualTimeOut) {
            $status = 'Missing Check-Out';
        }

        // Collect unique subjects from expected entries for display
        $subjects = collect($expectedEntries)
            ->filter(fn($e) => !empty($e['subject_code']))
            ->map(fn($e) => [
                'code' => $e['subject_code'],
                'desc' => $e['subject_desc'] ?? null,
            ])
            ->unique('code')
            ->values()
            ->toArray();

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
            'schedule_source' => $scheduleSource,
            'subjects' => $subjects,
        ];
    }

    /* ================================================================== */
    /*  Private helpers                                                   */
    /* ================================================================== */

    /**
     * Resolve the effective expected schedule entries for a given day.
     *
     * Priority:
     *   1. Approved ScheduleChangeRequests that override a schedule detail
     *      (effective_date <= target date)
     *   2. InternalSchedule operational entries
     *   (Official ScheduleDetail times are NOT used as fallback)
     *
     * Returns an array of ['time_in' => 'H:i:s', 'time_out' => 'H:i:s', 'source' => string]
     */
    private function resolveExpectedEntries(
        Faculty $faculty,
        $activeScheduleIds,
        string $dayOfWeek,
        Carbon $targetDate
    ): array {
        if ($activeScheduleIds->isEmpty()) {
            return [];
        }

        // Load schedule details for this day
        $scheduleDetails = ScheduleDetail::whereIn('schedule_id', $activeScheduleIds)
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('time_in')
            ->get();

        if ($scheduleDetails->isEmpty()) {
            // Check if there are change requests that MOVED a class TO this day
            return $this->getChangeRequestOnlyEntries($faculty, $dayOfWeek, $targetDate);
        }

        // Load approved change requests for these schedule details
        // that are effective on or before the target date
        $approvedChanges = ScheduleChangeRequest::where('faculty_id', $faculty->id)
            ->where('status', 'approved')
            ->whereIn('schedule_detail_id', $scheduleDetails->pluck('id'))
            ->where('effective_date', '<=', $targetDate)
            ->orderBy('effective_date', 'desc')
            ->get()
            ->keyBy('schedule_detail_id'); // latest approved change per detail

        // Build entries, applying change requests where applicable
        $entries = [];
        $overriddenDetailIds = [];

        foreach ($scheduleDetails as $detail) {
            $change = $approvedChanges->get($detail->id);

            if ($change) {
                // This detail has an approved change request
                $overriddenDetailIds[] = $detail->id;

                // If the change moved it to a DIFFERENT day, skip it on this day
                if ($change->requested_day_of_week !== $dayOfWeek) {
                    continue;
                }

                // Use the changed times
                $entries[] = [
                    'time_in' => Carbon::parse($change->requested_time_in)->format('H:i:s'),
                    'time_out' => Carbon::parse($change->requested_time_out)->format('H:i:s'),
                    'source' => 'change_request',
                    'subject_code' => $detail->subject_code ?? null,
                    'subject_desc' => $detail->subject_desc ?? null,
                ];
            } else {
                // No change request — use internal schedule only
                $entry = $this->getEntryFromInternal($faculty, $detail);
                if ($entry) {
                    $entries[] = $entry;
                }
            }
        }

        // Also check for change requests that MOVED a class FROM another day TO this day
        $movedToThisDay = ScheduleChangeRequest::where('faculty_id', $faculty->id)
            ->where('status', 'approved')
            ->where('requested_day_of_week', $dayOfWeek)
            ->where('effective_date', '<=', $targetDate)
            ->whereNotIn('schedule_detail_id', $overriddenDetailIds)
            ->get();

        foreach ($movedToThisDay as $change) {
            // Verify the original detail's day is different (it was moved here)
            $originalDetail = $change->scheduleDetail;
            if ($originalDetail && $originalDetail->day_of_week !== $dayOfWeek) {
                $entries[] = [
                    'time_in' => Carbon::parse($change->requested_time_in)->format('H:i:s'),
                    'time_out' => Carbon::parse($change->requested_time_out)->format('H:i:s'),
                    'source' => 'change_request',
                    'subject_code' => $originalDetail->subject_code ?? null,
                    'subject_desc' => $originalDetail->subject_desc ?? null,
                ];
            }
        }

        return $entries;
    }

    /**
     * Get entry from InternalSchedule only. Returns null if no operational internal schedule exists.
     */
    private function getEntryFromInternal(
        Faculty $faculty,
        ScheduleDetail $detail
    ): ?array {
        $internal = InternalSchedule::where('faculty_id', $faculty->id)
            ->where('schedule_id', $detail->schedule_id)
            ->where('day_of_week', $detail->day_of_week)
            ->where('is_operational', true)
            ->first();

        if (!$internal) {
            return null;
        }

        return [
            'time_in' => Carbon::parse($internal->device_time_in)->format('H:i:s'),
            'time_out' => $internal->device_time_out
                ? Carbon::parse($internal->device_time_out)->format('H:i:s')
                : Carbon::parse($internal->device_time_in)->addHours(3)->format('H:i:s'),
            'source' => 'internal',
            'subject_code' => $detail->subject_code ?? null,
            'subject_desc' => $detail->subject_desc ?? null,
        ];
    }

    /**
     * Handle the case where no schedule details exist on this day,
     * but a change request may have moved a class here.
     */
    private function getChangeRequestOnlyEntries(
        Faculty $faculty,
        string $dayOfWeek,
        Carbon $targetDate
    ): array {
        $movedHere = ScheduleChangeRequest::where('faculty_id', $faculty->id)
            ->where('status', 'approved')
            ->where('requested_day_of_week', $dayOfWeek)
            ->where('effective_date', '<=', $targetDate)
            ->get();

        $entries = [];
        foreach ($movedHere as $change) {
            $originalDetail = $change->scheduleDetail;
            if ($originalDetail && $originalDetail->day_of_week !== $dayOfWeek) {
                $entries[] = [
                    'time_in' => Carbon::parse($change->requested_time_in)->format('H:i:s'),
                    'time_out' => Carbon::parse($change->requested_time_out)->format('H:i:s'),
                    'source' => 'change_request',
                    'subject_code' => $originalDetail->subject_code ?? null,
                    'subject_desc' => $originalDetail->subject_desc ?? null,
                ];
            }
        }

        return $entries;
    }

    /**
     * Return the earliest of two Carbon instances (either may be null).
     */
    private function earliest(?Carbon $a, ?Carbon $b): ?Carbon
    {
        if (!$a)
            return $b;
        if (!$b)
            return $a;
        return $a->lessThanOrEqualTo($b) ? $a : $b;
    }

    /**
     * Return the latest of two Carbon instances (either may be null).
     */
    private function latest(?Carbon $a, ?Carbon $b): ?Carbon
    {
        if (!$a)
            return $b;
        if (!$b)
            return $a;
        return $a->greaterThanOrEqualTo($b) ? $a : $b;
    }
}
