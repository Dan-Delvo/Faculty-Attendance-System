<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminHolidayController;
use App\Http\Controllers\Admin\AdminAttendanceImportController;
use App\Http\Controllers\Admin\AdminNewPasswordController;
use App\Http\Controllers\Admin\AdminPasswordResetLinkController;
use App\Http\Controllers\Admin\AdminScheduleController;
use App\Http\Controllers\Admin\AdminScheduleChangeRequestController;
use App\Http\Controllers\Admin\AdminSessionController;
use Illuminate\Support\Facades\Route;

// ── Admin Guest routes (no auth required) ──────────────────────────────────
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminSessionController::class, 'create'])
        ->name('admin.login');

    Route::post('/login', [AdminSessionController::class, 'store'])
        ->name('admin.login.store');

    // ── Password Reset ─────────────────────────────────────────────────────
    Route::get('/forgot-password', [AdminPasswordResetLinkController::class, 'create'])
        ->name('admin.password.request');

    Route::post('/forgot-password', [AdminPasswordResetLinkController::class, 'store'])
        ->name('admin.password.email');

    Route::get('/reset-password/{token}', [AdminNewPasswordController::class, 'create'])
        ->name('admin.password.reset');

    Route::post('/reset-password', [AdminNewPasswordController::class, 'store'])
        ->name('admin.password.store');
});

// ── Admin Protected routes ─────────────────────────────────────────────────
Route::middleware(['auth.admin'])->prefix('admin')->group(function () {

    Route::post('/logout', [AdminSessionController::class, 'destroy'])
        ->name('admin.logout');

    // ── Dashboard ──────────────────────────────────────────────────────────
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');

    Route::get('/api/dashboard', [AdminDashboardController::class, 'liveStats'])
        ->name('admin.api.dashboard');

    // ── Schedule Management ────────────────────────────────────────────────
    Route::get('/schedules', [AdminScheduleController::class, 'index'])
        ->name('admin.schedules.index');

    Route::get('/schedules/suggestions', [AdminScheduleController::class, 'searchSuggestions'])
        ->name('admin.schedules.suggestions');

    Route::post('/schedules', [AdminScheduleController::class, 'store'])
        ->name('admin.schedules.store');

    Route::put('/schedules/{schedule}', [AdminScheduleController::class, 'update'])
        ->name('admin.schedules.update');

    Route::delete('/schedules/{schedule}', [AdminScheduleController::class, 'destroy'])
        ->name('admin.schedules.destroy');

    // ── Schedule Change Requests ───────────────────────────────────────────
    Route::get('/schedule-change-requests', [AdminScheduleChangeRequestController::class, 'index'])
        ->name('admin.schedule-change-requests.index');

    Route::get('/api/schedule-change-requests', [AdminScheduleChangeRequestController::class, 'filter'])
        ->name('admin.schedule-change-requests.filter');

    Route::patch('/schedule-change-requests/{scheduleChangeRequest}/approve', [AdminScheduleChangeRequestController::class, 'approve'])
        ->name('admin.schedule-change-requests.approve');

    Route::patch('/schedule-change-requests/{scheduleChangeRequest}/reject', [AdminScheduleChangeRequestController::class, 'reject'])
        ->name('admin.schedule-change-requests.reject');

    // ── Holiday Management ─────────────────────────────────────────────────
    Route::get('/holidays', [AdminHolidayController::class, 'index'])
        ->name('admin.holidays.index');

    Route::post('/holidays', [AdminHolidayController::class, 'store'])
        ->name('admin.holidays.store');

    Route::put('/holidays/{holiday}', [AdminHolidayController::class, 'update'])
        ->name('admin.holidays.update');

    Route::delete('/holidays/{holiday}', [AdminHolidayController::class, 'destroy'])
        ->name('admin.holidays.destroy');

    // ── Attendance Log Import ─────────────────────────────────────────────
    Route::get('/attendance-imports', [AdminAttendanceImportController::class, 'index'])
        ->name('admin.attendance-imports.index');

    Route::post('/attendance-imports', [AdminAttendanceImportController::class, 'store'])
        ->name('admin.attendance-imports.store');

    Route::get('/attendance-imports/template', [AdminAttendanceImportController::class, 'downloadTemplate'])
        ->name('admin.attendance-imports.template');
});
