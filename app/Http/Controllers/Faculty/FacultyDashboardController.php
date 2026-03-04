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
                'biometricLogs' => [],
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
            'biometricLogs' => $faculty->getFormattedBiometricLogs(),
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
    public function biometricLogs(Request $request)
    {
        $faculty = $request->user()->faculty;

        return Inertia::render('Faculty/BiometricLogs', [
            'biometricLogs' => $faculty ? $faculty->getFormattedBiometricLogs(100) : [],
            'monthlyAverages' => $faculty ? $faculty->getMonthlyAverages() : ['avgCheckIn' => '--:--', 'avgCheckOut' => '--:--'],
        ]);
    }

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
                ->with('scheduleDetail')
                ->orderBy('attendance_date', 'desc')
                ->get();

            $attendanceLogs = $records->map(function ($record) {
                $detail = $record->scheduleDetail;

                // Build subjects array from the linked schedule detail
                $subjects = [];
                if ($detail && $detail->subject_code) {
                    $subjects[] = [
                        'code' => $detail->subject_code,
                        'desc' => $detail->subject_desc ?? null,
                    ];
                }

                // Format hours rendered
                $totalMinutes = (int) round((float) $record->total_hours_rendered * 60);
                $hours = intdiv($totalMinutes, 60);
                $mins = $totalMinutes % 60;
                $totalHours = ($hours > 0 ? $hours . 'h ' : '') . $mins . 'm';

                return [
                    'date' => $record->attendance_date->format('M d, Y'),
                    'raw_date' => $record->attendance_date->toDateString(),
                    'dayOfWeek' => $record->attendance_date->format('l'),
                    'status' => $record->status,
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
                    'undertime_minutes' => $record->undertime_minutes ?? 0,
                    'overtime_minutes' => $record->overtime_minutes ?? 0,
                    'night_minutes' => $record->night_minutes ?? 0,
                    'overtime_night_minutes' => $record->overtime_night_minutes ?? 0,
                    'required_hours' => (float) $record->required_hours,
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
}
