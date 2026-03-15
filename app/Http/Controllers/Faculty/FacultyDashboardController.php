<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Services\AttendanceReconciliationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FacultyDashboardController extends Controller
{
    /**
     * Display the faculty dashboard.
     *
     * The controller stays thin — all data logic lives in the Faculty model.
     */
    public function index(Request $request, AttendanceReconciliationService $service)
    {
        /** @var Faculty|null $faculty */
        $faculty = $request->user()->faculty;

        // Fallback for users without a faculty profile
        if (!$faculty) {
            return Inertia::render('Faculty/Dashboard', [
                'stats' => [],
                'todaySchedule' => [],
                'recentAttendance' => [],
                'checkInTrend' => [],
                'monthlyAverages' => ['avgCheckIn' => '--:--', 'avgCheckOut' => '--:--'],
                'currentDate' => Carbon::now()->format('l, F j, Y'),
                'greeting' => $this->getGreeting(),
            ]);
        }

        // Determine trend range (months)
        $range = $request->query('range', 'Last 6 months');
        $months = 6;

        switch ($range) {
            case 'Last 3 months':
                $months = 3;
                break;
            case 'Last 12 months':
                $months = 12;
                break;
            case 'This Year':
                $months = (int) Carbon::now()->month;
                break;
            default:
                $months = 6;
                break;
        }

        // Fetch the 20 most recent actual attendance records directly from the table based on internal schedule logic
        $recentAttendance = $faculty->getRecentAttendance(20);

        return Inertia::render('Faculty/Dashboard', [
            'stats' => $faculty->getDashboardStats(),
            'todaySchedule' => $faculty->getTodayScheduleDetails(),
            'recentAttendance' => $recentAttendance,
            'checkInTrend' => $faculty->getCheckInTrend($months),
            'monthlyAverages' => $faculty->getMonthlyAverages(),
            'currentDate' => Carbon::now()->format('l, F j, Y'),
            'greeting' => $this->getGreeting(),
            'filters' => [
                'range' => $range
            ]
        ]);
    }

    /**
     * AJAX endpoint for dashboard analytics.
     */
    public function getAnalyticsData(Request $request)
    {
        $faculty = $request->user()->faculty;
        if (!$faculty)
            return response()->json([], 404);

        $range = $request->query('range', 'Last 6 months');
        $months = match ($range) {
            'Last 3 months' => 3,
            'Last 12 months' => 12,
            'This Year' => (int) Carbon::now()->month,
            default => 6,
        };

        return response()->json([
            'checkInTrend' => $faculty->getCheckInTrend($months),
            'monthlyAverages' => $faculty->getMonthlyAverages(),
        ]);
    }

    /**
     * Display the full biometric logs page.
     */


    /**
     * Display the schedule & attendance page.
     */
    public function schedule(Request $request)
    {
        $faculty = $request->user()->faculty;

        if (!$faculty) {
            return Inertia::render('Faculty/Schedule', [
                'weeklySchedule' => [],
                'internalSchedule' => [],
                'facultyName' => '',
            ]);
        }

        return Inertia::render('Faculty/Schedule', [
            'weeklySchedule' => $faculty->getWeeklySchedule(),
            'internalSchedule' => $faculty->getWeeklyInternalSchedule(),
            'facultyName' => $faculty->full_name,
        ]);
    }

    /**
     * Display the new matched attendance page.
     * Evaluates attendance records dynamically using the service.
     */
    public function attendance(Request $request)
    {
        $faculty = $request->user()->faculty;

        $attendanceLogs = [];

        if ($faculty) {
            $records = \App\Models\AttendanceRecord::where('faculty_id', $faculty->id)
                ->with(['scheduleDetail', 'justifications'])
                ->orderBy('attendance_date', 'desc')
                ->get();

            $attendanceLogs = $records->map(function ($record) {
                $detail = $record->scheduleDetail;

                // Build subjects array from the linked schedule detail
                $subjects = [];
                if ($detail) {
                    $subjects[] = [
                        'code' => $detail->course_code ?? $detail->subject_code ?? '',
                        'desc' => $detail->subject_desc ?? null,
                        'program_code' => $detail->program_code,
                        'year_level' => $detail->year_level,
                        'section_name' => $detail->section_name,
                    ];
                }

                // Format hours rendered
                $totalMinutes = (int) round((float) $record->total_hours_rendered * 60);
                $hours = intdiv($totalMinutes, 60);
                $mins = $totalMinutes % 60;
                $totalHours = ($hours > 0 ? $hours . 'h ' : '') . $mins . 'm';

                // Find undertime justification if any
                $undertimeJustification = $record->justifications
                    ->where('type', 'undertime')
                    ->first();

                $missingTimeJustification = $record->justifications
                    ->where('type', 'missing_time')
                    ->first();

                // Calculate undertime minutes on the fly for UI consistency if DB column is out of sync
                $undertimeMinutes = $record->undertime_minutes ?? 0;
                if ($record->actual_time_out && $record->operational_time_out && $undertimeMinutes == 0) {
                    if ($record->actual_time_out->lt($record->operational_time_out)) {
                        $undertimeMinutes = $record->actual_time_out->diffInMinutes($record->operational_time_out);
                    }
                }

                // Dynamically adjust status for UI consistency if DB status is out of sync
                $displayStatus = $record->status;
                $isUndertime = ($undertimeMinutes > 0);
                $isOvertime = ($record->overtime_minutes > 0);

                if (strtolower($displayStatus) === 'present') {
                    if ($isUndertime && $isOvertime) {
                        $displayStatus = 'UNDERTIME / OVERTIME';
                    } elseif ($isUndertime) {
                        $displayStatus = 'UNDERTIME';
                    } elseif ($isOvertime) {
                        $displayStatus = 'OVERTIME';
                    }
                }

                return [
                    'id' => $record->id,
                    'date' => $record->attendance_date->format('M d, Y'),
                    'raw_date' => $record->attendance_date->toDateString(),
                    'dayOfWeek' => $record->attendance_date->format('l'),
                    'status' => $displayStatus,
                    'expected_time_in' => $record->operational_time_in
                        ? $record->operational_time_in->format('h:i A')
                        : ($record->official_time_in ? $record->official_time_in->format('h:i A') : '--:--'),
                    'expected_time_out' => $record->operational_time_out
                        ? $record->operational_time_out->format('h:i A')
                        : ($record->official_time_out ? $record->official_time_out->format('h:i A') : '--:--'),
                    'actual_time_in' => $record->actual_time_in
                        ? $record->actual_time_in->format('h:i A') : '--:--',
                    'actual_time_out' => $record->actual_time_out
                        ? $record->actual_time_out->format('h:i A') : '--:--',
                    'late_minutes' => $record->late_minutes ?? 0,
                    'undertime_minutes' => (int) $undertimeMinutes,
                    'undertime_justification' => $undertimeJustification?->justification,
                    'undertime_status' => $undertimeJustification?->status,
                    'missing_time_justification' => $missingTimeJustification?->justification,
                    'missing_time_status' => $missingTimeJustification?->status,
                    'updated_at' => ($undertimeJustification || $missingTimeJustification)
                        ? ($undertimeJustification?->updated_at ?? $missingTimeJustification->updated_at)->toIso8601String()
                        : ($record->updated_at ? $record->updated_at->toIso8601String() : null),
                    'overtime_minutes' => $record->overtime_minutes ?? 0,
                    'night_minutes' => $record->night_minutes ?? 0,
                    'overtime_night_minutes' => $record->overtime_night_minutes ?? 0,
                    'required_hours' => ($record->required_hours <= 0 && $record->operational_time_out && $record->operational_time_in)
                        ? (float) max(0, (int) round($record->operational_time_out->diffInMinutes($record->operational_time_in) / 60))
                        : (float) $record->required_hours,
                    'total_hours' => $totalHours,
                    'online_attendance' => false,
                    'subjects' => $subjects,
                ];
            })->toArray();
        }

        return Inertia::render('Faculty/Attendance', [
            'attendanceLogs' => $attendanceLogs,
        ]);
    }

    /**
     * Return a time-aware greeting string.
     */
    private function getGreeting(): string
    {
        $hour = Carbon::now()->hour;

        if ($hour < 12) {
            return 'Good Morning';
        } elseif ($hour < 17) {
            return 'Good Afternoon';
        }

        return 'Good Evening';
    }

    /**
     * Submit undertime justification.
     */
    public function submitUndertimeJustification(Request $request, $id)
    {
        $request->validate([
            'justification' => 'required|string|max:1000',
        ]);

        $faculty = $request->user()->faculty;
        if (!$faculty)
            abort(403);

        $record = \App\Models\AttendanceRecord::where('faculty_id', $faculty->id)
            ->findOrFail($id);

        // Check if there is actually undertime (dynamically check if DB is out of sync)
        $hasUndertime = ($record->undertime_minutes > 0);
        if (!$hasUndertime && $record->actual_time_out && $record->operational_time_out) {
            $hasUndertime = $record->actual_time_out->lt($record->operational_time_out);
        }

        if (!$hasUndertime) {
            return back()->with('error', 'No undertime to justify for this record.');
        }

        $justification = $record->justifications()
            ->where('type', 'undertime')
            ->first();

        if ($justification && in_array($justification->status, ['approved', 'rejected'])) {
            return back()->with('error', 'Cannot edit a justification that has already been reviewed.');
        }

        $isUpdate = $justification && $justification->status === 'pending';

        if ($isUpdate && $justification->updated_at) {
            $diffInMinutes = now()->diffInMinutes($justification->updated_at);
            if ($diffInMinutes > 15) {
                return back()->with('error', 'The 15-minute window to edit this justification has expired.');
            }
        }

        $record->justifications()->updateOrCreate(
            ['type' => 'undertime'],
            [
                'faculty_id' => $faculty->id,
                'justification' => $request->justification,
                'status' => 'pending',
            ]
        );

        return back()->with('success', $isUpdate ? 'Justification updated successfully.' : 'Justification submitted. Pending approval from Head.');
    }

    /**
     * Submit missing time justification.
     */
    public function submitMissingTimeJustification(Request $request, $id)
    {
        $request->validate([
            'justification' => 'required|string|max:1000',
        ]);

        $faculty = $request->user()->faculty;
        if (!$faculty)
            abort(403);

        $record = \App\Models\AttendanceRecord::where('faculty_id', $faculty->id)
            ->findOrFail($id);

        // Check if there is actually a missing time in or out
        $isMissing = !$record->actual_time_in || !$record->actual_time_out
            || $record->actual_time_in->format('H:i:s') === '00:00:00' // Assuming --:-- might be stored as midnight in some cases, but actually controller shows format check
        ;

        // Re-check based on what we send to frontend
        $actualIn = $record->actual_time_in ? $record->actual_time_in->format('h:i A') : '--:--';
        $actualOut = $record->actual_time_out ? $record->actual_time_out->format('h:i A') : '--:--';
        $isMissing = ($actualIn === '--:--' || $actualOut === '--:--');

        if (!$isMissing) {
            return back()->with('error', 'No missing time in or out to justify for this record.');
        }

        $justification = $record->justifications()
            ->where('type', 'missing_time')
            ->first();

        if ($justification && in_array($justification->status, ['approved', 'rejected'])) {
            return back()->with('error', 'Cannot edit a justification that has already been reviewed.');
        }

        $isUpdate = $justification && $justification->status === 'pending';

        if ($isUpdate && $justification->updated_at) {
            $diffInMinutes = now()->diffInMinutes($justification->updated_at);
            if ($diffInMinutes > 15) {
                return back()->with('error', 'The 15-minute window to edit this justification has expired.');
            }
        }

        $record->justifications()->updateOrCreate(
            ['type' => 'missing_time'],
            [
                'faculty_id' => $faculty->id,
                'justification' => $request->justification,
                'status' => 'pending',
            ]
        );

        return back()->with('success', $isUpdate ? 'Justification updated successfully.' : 'Justification submitted. Pending approval from Head.');
    }
}
