<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FacultyController extends Controller
{
    /**
     * Display the faculty dashboard.
     *
     * The controller stays thin — all data logic lives in the Faculty model.
     */
    public function index(Request $request)
    {
        /** @var Faculty|null $faculty */
        $faculty = $request->user()->faculty;

        // Fallback for users without a faculty profile
        if (!$faculty) {
            return Inertia::render('Faculty/Dashboard', [
                'stats' => [],
                'todaySchedule' => [],
                'biometricLogs' => [],
                'checkInTrend' => [],
                'monthlyAverages' => ['avgCheckIn' => '--:--', 'avgCheckOut' => '--:--'],
                'currentDate' => Carbon::now()->format('l, F j, Y'),
                'greeting' => $this->getGreeting(),
            ]);
        }

        return Inertia::render('Faculty/Dashboard', [
            'stats' => $faculty->getDashboardStats(),
            'todaySchedule' => $faculty->getTodayScheduleDetails(),
            'biometricLogs' => $faculty->getFormattedBiometricLogs(),
            'checkInTrend' => $faculty->getCheckInTrend(),
            'monthlyAverages' => $faculty->getMonthlyAverages(),
            'currentDate' => Carbon::now()->format('l, F j, Y'),
            'greeting' => $this->getGreeting(),
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
                'attendanceRecords' => [],
                'facultyName' => '',
            ]);
        }

        return Inertia::render('Faculty/Schedule', [
            'weeklySchedule' => $faculty->getWeeklySchedule(),
            'attendanceRecords' => $faculty->getRecentAttendance(20),
            'facultyName' => $faculty->full_name,
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
