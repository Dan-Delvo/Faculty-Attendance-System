<?php
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
Route::delete('/faculty/{id}', [FacultyController::class, 'destroy'])
    ✅ Example of how to use the check.permission middleware with a specific permission and guard:
    ->middleware('check.permission:' . Permission::DeleteFaculty->value . ',admin')
    ->name('faculty.destroy');
*/
