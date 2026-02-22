<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminScheduleController;
use App\Http\Controllers\Admin\AdminSessionController;
use Illuminate\Support\Facades\Route;

// ── Admin Guest routes (no auth required) ──────────────────────────────────
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminSessionController::class, 'create'])
        ->name('admin.login');

    Route::post('/login', [AdminSessionController::class, 'store'])
        ->name('admin.login.store');
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
});
