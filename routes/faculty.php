<?php
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\FacultyController;

Route::middleware(['auth', 'auth.faculty'])->group(function () {
    Route::get('/faculty/dashboard', [FacultyController::class, 'index'])->name('faculty.dashboard');
    Route::get('/faculty/biometric-logs', [FacultyController::class, 'biometricLogs'])->name('faculty.biometric-logs');
    Route::get('/faculty/schedule', [FacultyController::class, 'schedule'])->name('faculty.schedule');
});

/*
Route::delete('/faculty/{id}', [FacultyController::class, 'destroy'])
    ✅ Example of how to use the check.permission middleware with a specific permission and guard:
    ->middleware('check.permission:' . Permission::DeleteFaculty->value . ',admin')
    ->name('faculty.destroy');
*/
