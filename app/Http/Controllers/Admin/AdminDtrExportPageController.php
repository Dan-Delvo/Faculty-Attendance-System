<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Carbon\Carbon;
use Inertia\Inertia;

class AdminDtrExportPageController extends Controller
{
    public function index()
    {
        $activeFaculty = Faculty::getActiveFacultyList();
        $now = Carbon::now();

        return Inertia::render('Admin/DtrExport', [
            'facultyOptions' => $activeFaculty,
            'dtrExportDefaults' => [
                'faculty_id' => $activeFaculty[0]['id'] ?? null,
                'month' => $now->month,
                'year' => $now->year,
            ],
            'dtrExportYears' => array_values(array_reverse(range($now->year - 5, $now->year + 1))),
        ]);
    }
}
