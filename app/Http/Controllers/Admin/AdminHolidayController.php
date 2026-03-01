<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminHolidayController extends Controller
{
    /**
     * Display the holiday management page.
     */
    public function index(Request $request)
    {
        $query = Holiday::query()->orderBy('holiday_date', 'asc');

        if ($search = $request->query('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($year = $request->query('year')) {
            $query->whereYear('holiday_date', $year);
        }

        $holidays = $query->paginate($request->query('per_page', 15))->withQueryString();

        return Inertia::render('Admin/Holidays', [
            'holidays' => $holidays,
            'filters'  => [
                'search'   => $request->query('search', ''),
                'type'     => $request->query('type', ''),
                'year'     => $request->query('year', ''),
                'per_page' => $request->query('per_page', 15),
            ],
        ]);
    }

    /**
     * Store a newly created holiday.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'holiday_date' => ['required', 'date', Rule::unique('holidays', 'holiday_date')->whereNull('deleted_at')],
            'name'         => ['required', 'string', 'max:255'],
            'type'         => ['required', Rule::in(['national', 'local', 'observance'])],
            'is_recurring' => ['boolean'],
        ]);

        Holiday::create($validated);

        return back()->with('success', 'Holiday "' . $validated['name'] . '" has been added.');
    }

    /**
     * Update the specified holiday.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'holiday_date' => [
                'required',
                'date',
                Rule::unique('holidays', 'holiday_date')
                    ->ignore($holiday->id)
                    ->whereNull('deleted_at'),
            ],
            'name'         => ['required', 'string', 'max:255'],
            'type'         => ['required', Rule::in(['national', 'local', 'observance'])],
            'is_recurring' => ['boolean'],
        ]);

        $holiday->update($validated);

        return back()->with('success', 'Holiday "' . $validated['name'] . '" has been updated.');
    }

    /**
     * Remove the specified holiday (soft delete).
     */
    public function destroy(Holiday $holiday)
    {
        $name = $holiday->name;
        $holiday->delete();

        return back()->with('success', 'Holiday "' . $name . '" has been removed.');
    }
}
