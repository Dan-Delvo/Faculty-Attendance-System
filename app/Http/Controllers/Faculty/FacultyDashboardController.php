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

        // Fetch the 20 most recent actual attendance records
        $recentAttendance = [];
        $earliestLog = \App\Models\BiometricLog::where('biometric_id', $faculty->biometric_id)
            ->orderBy('log_datetime', 'asc')
            ->first();

        $startDate = $earliestLog ? Carbon::parse($earliestLog->log_datetime)->startOfDay() : Carbon::today()->subDays(60);
        $endDate = Carbon::today();

        for ($date = $endDate->copy(); $date->greaterThanOrEqualTo($startDate); $date->subDay()) {
            if (count($recentAttendance) >= 20)
                break;

            $targetDate = $date->toDateString();
            $statusData = $service->getDailyAttendanceStatus($faculty, $targetDate);

            if ($statusData['status'] !== 'No Schedule') {
                $recentAttendance[] = array_merge([
                    'date' => Carbon::parse($targetDate)->format('M d, Y'),
                    'raw_date' => $targetDate,
                    'dayOfWeek' => Carbon::parse($targetDate)->format('l'),
                ], $statusData);
            }
        }

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
                'facultyName' => '',
            ]);
        }

        return Inertia::render('Faculty/Schedule', [
            'weeklySchedule' => $faculty->getWeeklySchedule(),
            'facultyName' => $faculty->full_name,
        ]);
    }

    /**
     * Display the new matched attendance page.
     * Evaluates attendance records dynamically using the service.
     */
    public function attendance(Request $request, AttendanceReconciliationService $service)
    {
        $faculty = $request->user()->faculty;

        $attendanceLogs = [];
        if ($faculty) {
            $earliestLog = \App\Models\BiometricLog::where('biometric_id', $faculty->biometric_id)
                ->orderBy('log_datetime', 'asc')
                ->first();

            $startDate = $earliestLog ? Carbon::parse($earliestLog->log_datetime)->startOfDay() : Carbon::today()->subDays(30);
            $endDate = Carbon::today();

            for ($date = $endDate->copy(); $date->greaterThanOrEqualTo($startDate); $date->subDay()) {
                $targetDate = $date->toDateString();

                // Fetch status
                $statusData = $service->getDailyAttendanceStatus($faculty, $targetDate);

                if ($statusData['status'] !== 'No Schedule') {
                    $attendanceLogs[] = array_merge([
                        'date' => Carbon::parse($targetDate)->format('M d, Y'),
                        'raw_date' => $targetDate,
                        'dayOfWeek' => Carbon::parse($targetDate)->format('l'),
                    ], $statusData);
                }
            }
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
