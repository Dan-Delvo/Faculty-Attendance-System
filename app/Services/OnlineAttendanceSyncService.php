<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\InternalSchedule;
use App\Models\OnlineAttendanceRequest;
use App\Models\ScheduleDetail;
use Carbon\Carbon;
use RuntimeException;

class OnlineAttendanceSyncService
{
    /**
     * Sync approved online attendance times into attendance_records.
     * Matching is done without FK by faculty_id + attendance_date (+ schedule_detail_id when available).
     *
     * @throws RuntimeException
     */
    public function syncApprovedRequest(OnlineAttendanceRequest $request): void
    {
        if ($request->status !== 'approved') {
            return;
        }

        // Primary path: use direct linkage to the online request.
        $attendanceRecord = AttendanceRecord::query()
            ->where('online_attendance_id', $request->id)
            ->lockForUpdate()
            ->first();

        // Backward compatibility path for old rows that were synced before FK existed.
        if (!$attendanceRecord) {
            $attendanceRecord = $this->findLegacyAttendanceRecord($request);

            if ($attendanceRecord) {
                if (
                    $attendanceRecord->online_attendance_id !== null
                    && (int) $attendanceRecord->online_attendance_id !== (int) $request->id
                ) {
                    throw new RuntimeException('Attendance record is already linked to another online attendance request.');
                }

                $attendanceRecord->online_attendance_id = $request->id;
            }
        }

        if (!$attendanceRecord) {
            $attendanceRecord = $this->createAttendanceRecordFromOnlineRequest($request);
        }

        $attendanceRecord->online_attendance_id = $request->id;

        if (!$attendanceRecord->internal_schedule_id) {
            $attendanceRecord->internal_schedule_id = $this->resolveInternalScheduleId($request);
        }

        $attendanceDate = Carbon::parse($request->attendance_date)->toDateString();

        // Business rule: for approved online attendance, normalize actual times to operational schedule times.
        $actualTimeIn = $attendanceRecord->operational_time_in
            ? Carbon::parse($attendanceRecord->operational_time_in)
            : Carbon::parse($attendanceDate . ' ' . Carbon::parse($request->time_in)->format('H:i:s'));

        $actualTimeOut = $attendanceRecord->operational_time_out
            ? Carbon::parse($attendanceRecord->operational_time_out)
            : Carbon::parse($attendanceDate . ' ' . Carbon::parse($request->time_out)->format('H:i:s'));

        $attendanceRecord->actual_time_in = $actualTimeIn;
        $attendanceRecord->actual_time_out = $actualTimeOut;

        $lateMinutes = 0;
        $undertimeMinutes = 0;
        $overtimeMinutes = 0;

        $attendanceRecord->late_minutes = $lateMinutes;
        $attendanceRecord->undertime_minutes = $undertimeMinutes;
        $attendanceRecord->overtime_minutes = $overtimeMinutes;

        $totalMinutesRendered = max(0, $actualTimeIn->diffInMinutes($actualTimeOut, false));
        $attendanceRecord->total_hours_rendered = round($totalMinutesRendered / 60, 2);

        $attendanceRecord->status = 'present';

        $attendanceRecord->save();
    }

    /**
     * Find pre-existing attendance rows created before online_attendance_id linkage existed.
     *
     * @throws RuntimeException
     */
    private function findLegacyAttendanceRecord(OnlineAttendanceRequest $request): ?AttendanceRecord
    {
        $attendanceQuery = AttendanceRecord::query()
            ->where('faculty_id', $request->faculty_id)
            ->whereDate('attendance_date', $request->attendance_date);

        if ($request->schedule_detail_id) {
            $attendanceQuery->where('schedule_detail_id', $request->schedule_detail_id);
        }

        $attendanceRecord = $attendanceQuery->lockForUpdate()->first();

        // Fallback for old rows with missing schedule_detail_id, but keep it safe against ambiguous matches.
        if (!$attendanceRecord && $request->schedule_detail_id) {
            $fallbackQuery = AttendanceRecord::query()
                ->where('faculty_id', $request->faculty_id)
                ->whereDate('attendance_date', $request->attendance_date)
                ->whereNull('online_attendance_id');

            $fallbackCount = (clone $fallbackQuery)->count();
            if ($fallbackCount > 1) {
                throw new RuntimeException('Multiple legacy attendance records found for this faculty and date. Please resolve duplicates first.');
            }

            $attendanceRecord = $fallbackQuery->lockForUpdate()->first();
        }

        return $attendanceRecord;
    }

    /**
     * Create a baseline attendance record when none exists yet for the approved online request.
     */
    private function createAttendanceRecordFromOnlineRequest(OnlineAttendanceRequest $request): AttendanceRecord
    {
        $attendanceDate = Carbon::parse($request->attendance_date)->toDateString();
        $scheduleDetail = null;

        if ($request->schedule_detail_id) {
            $scheduleDetail = ScheduleDetail::find($request->schedule_detail_id);
        }

        $resolvedInternalScheduleId = $this->resolveInternalScheduleId($request, $scheduleDetail);

        $officialIn = $scheduleDetail?->start_time
            ? Carbon::parse($attendanceDate . ' ' . Carbon::parse($scheduleDetail->start_time)->format('H:i:s'))
            : Carbon::parse($attendanceDate . ' ' . $request->time_in);

        $officialOut = $scheduleDetail?->end_time
            ? Carbon::parse($attendanceDate . ' ' . Carbon::parse($scheduleDetail->end_time)->format('H:i:s'))
            : Carbon::parse($attendanceDate . ' ' . $request->time_out);

        $requiredHours = round(max(0, $officialIn->diffInMinutes($officialOut, false)) / 60, 2);

        return AttendanceRecord::create([
            'faculty_id' => $request->faculty_id,
            'online_attendance_id' => $request->id,
            'schedule_detail_id' => $request->schedule_detail_id,
            'internal_schedule_id' => $resolvedInternalScheduleId,
            'attendance_date' => $attendanceDate,
            'day_of_week' => Carbon::parse($attendanceDate)->format('l'),
            'official_time_in' => $officialIn,
            'official_time_out' => $officialOut,
            'operational_day_of_week' => Carbon::parse($attendanceDate)->format('l'),
            'operational_time_in' => $officialIn,
            'operational_time_out' => $officialOut,
            'actual_time_in' => null,
            'actual_time_out' => null,
            'late_minutes' => 0,
            'undertime_minutes' => 0,
            'overtime_minutes' => 0,
            'night_minutes' => 0,
            'overtime_night_minutes' => 0,
            'total_hours_rendered' => 0,
            'required_hours' => $requiredHours,
            'status' => 'absent',
            'remarks' => 'Auto-created from approved online attendance request',
            'is_manual_entry' => false,
            'processed_at' => now(),
        ]);
    }

    /**
     * Resolve internal schedule id for the request date.
     * Priority: exact (faculty + schedule + day) then fallback (faculty + day).
     */
    private function resolveInternalScheduleId(
        OnlineAttendanceRequest $request,
        ?ScheduleDetail $scheduleDetail = null
    ): ?int {
        $dayOfWeek = Carbon::parse($request->attendance_date)->format('l');
        $scheduleDetail = $scheduleDetail ?? ($request->schedule_detail_id ? ScheduleDetail::find($request->schedule_detail_id) : null);

        if ($scheduleDetail?->schedule_id) {
            $exact = InternalSchedule::query()
                ->where('faculty_id', $request->faculty_id)
                ->where('schedule_id', $scheduleDetail->schedule_id)
                ->where('day_of_week', $dayOfWeek)
                ->where('is_operational', true)
                ->orderByDesc('id')
                ->first();

            if ($exact) {
                return (int) $exact->id;
            }
        }

        $fallback = InternalSchedule::query()
            ->where('faculty_id', $request->faculty_id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_operational', true)
            ->orderByDesc('id')
            ->first();

        return $fallback ? (int) $fallback->id : null;
    }
}
