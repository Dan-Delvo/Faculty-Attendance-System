<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\BiometricLog;
use App\Models\Faculty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(Request $request)
    {
        return Inertia::render('Admin/Dashboard', [
            'stats'             => Faculty::getAdminDashboardStats(),
            'timedInFaculties'  => Faculty::getCurrentlyTimedIn(),
            'timedOutFaculties' => Faculty::getCurrentlyTimedOut(),
            'weeklyGraph'       => Faculty::getWeeklyTimedInGraph(),
            'currentDate'       => Carbon::now()->format('l, F j, Y'),
            'greeting'          => $this->getGreeting(),
        ]);
    }

    /**
     * AJAX endpoint for real-time dashboard data updates.
     */
    public function liveStats(Request $request)
    {
        return response()->json([
            'stats'             => Faculty::getAdminDashboardStats(),
            'timedInFaculties'  => Faculty::getCurrentlyTimedIn(),
            'timedOutFaculties' => Faculty::getCurrentlyTimedOut(),
            'weeklyGraph'       => Faculty::getWeeklyTimedInGraph(),
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
