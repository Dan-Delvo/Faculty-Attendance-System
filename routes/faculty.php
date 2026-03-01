<?php
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Faculty\FacultyDashboardController;
use App\Http\Controllers\Faculty\ScheduleChangeRequestController;
use App\Http\Controllers\Faculty\OnlineAttendanceController;

Route::middleware(['auth', 'auth.faculty'])->group(function () {
    Route::get('/faculty/dashboard', [FacultyDashboardController::class, 'index'])->name('faculty.dashboard');
    Route::get('/faculty/api/analytics', [FacultyDashboardController::class, 'getAnalyticsData'])->name('faculty.api.analytics');
    Route::get('/faculty/biometric-logs', [FacultyDashboardController::class, 'biometricLogs'])->name('faculty.biometric-logs');
    Route::get('/faculty/schedule', [FacultyDashboardController::class, 'schedule'])->name('faculty.schedule');
    Route::get('/faculty/attendance', [FacultyDashboardController::class, 'attendance'])->name('faculty.attendance');

    // ── Schedule Change Requests ───────────────────────────────────────────
    Route::get('/faculty/schedule-change-requests', [ScheduleChangeRequestController::class, 'index'])
        ->name('faculty.schedule-change-requests.index');
    Route::post('/faculty/schedule-change-requests', [ScheduleChangeRequestController::class, 'store'])
        ->name('faculty.schedule-change-requests.store');
    Route::delete('/faculty/schedule-change-requests/{scheduleChangeRequest}', [ScheduleChangeRequestController::class, 'destroy'])
        ->name('faculty.schedule-change-requests.destroy');

    // AJAX endpoints for schedule change requests
    Route::get('/faculty/api/schedule-change-requests', [ScheduleChangeRequestController::class, 'filter'])
        ->name('faculty.schedule-change-requests.filter');
    Route::post('/faculty/api/schedule-change-requests/check-conflict', [ScheduleChangeRequestController::class, 'checkConflict'])
        ->name('faculty.schedule-change-requests.check-conflict');

    // ── Online Attendance Requests ─────────────────────────────────────────
    Route::get('/faculty/online-attendance', [OnlineAttendanceController::class, 'index'])
        ->name('faculty.online-attendance.index');
    Route::post('/faculty/online-attendance', [OnlineAttendanceController::class, 'store'])
        ->name('faculty.online-attendance.store');
    Route::delete('/faculty/online-attendance/{onlineAttendanceRequest}', [OnlineAttendanceController::class, 'destroy'])
        ->name('faculty.online-attendance.destroy');

    // AJAX endpoints for online attendance
    Route::get('/faculty/api/online-attendance', [OnlineAttendanceController::class, 'filter'])
        ->name('faculty.online-attendance.filter');
});

/*
Route::delete('/faculty/{id}', [FacultyController::class, 'destroy'])
    ✅ Example of how to use the check.permission middleware with a specific permission and guard:
    ->middleware('check.permission:' . Permission::DeleteFaculty->value . ',admin')
    ->name('faculty.destroy');
*/
