<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\ScheduleChangeRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleChangeRequestController extends Controller
{
    /**
     * Display the faculty's schedule change requests page.
     */
    public function index(Request $request)
    {
        $faculty = $request->user()->faculty;

        if (!$faculty) {
            return Inertia::render('Faculty/ScheduleChangeRequests', [
                'requests'        => ['data' => [], 'total' => 0, 'per_page' => 10, 'current_page' => 1, 'last_page' => 1],
                'scheduleDetails' => [],
                'filters'         => ['status' => ''],
            ]);
        }

        return Inertia::render('Faculty/ScheduleChangeRequests', [
            'requests'        => ScheduleChangeRequest::getForFaculty($faculty->id, $request),
            'scheduleDetails' => $faculty->getScheduleDetailsForChangeRequest(),
            'filters'         => [
                'status' => $request->query('status', ''),
            ],
        ]);
    }

    /**
     * AJAX endpoint: return filtered & paginated requests as JSON.
     */
    public function filter(Request $request)
    {
        $faculty = $request->user()->faculty;

        if (!$faculty) {
            return response()->json([
                'data' => [], 'total' => 0, 'per_page' => 10, 'current_page' => 1, 'last_page' => 1,
            ]);
        }

        return response()->json(
            ScheduleChangeRequest::getForFaculty($faculty->id, $request)
        );
    }

    /**
     * Store a new schedule change request.
     */
    public function store(Request $request)
    {
        $faculty = $request->user()->faculty;

        if (!$faculty) {
            return back()->withErrors(['error' => 'Faculty profile not found.']);
        }

        $validated = $request->validate([
            'schedule_detail_id'    => 'required|exists:schedule_details,id',
            'requested_day_of_week' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'requested_time_in'     => 'required|date_format:H:i',
            'requested_time_out'    => 'required|date_format:H:i|after:requested_time_in',
            'requested_room'        => 'nullable|string|max:100',
            'effective_date'        => 'required|date|after_or_equal:today',
            'reason'                => 'required|string|max:1000',
        ]);

        $result = $faculty->createScheduleChangeRequest($validated);

        if (!$result['success']) {
            return back()->withErrors([$result['error_field'] => $result['error_message']]);
        }

        return back()->with('success', 'Schedule change request submitted successfully.');
    }

    /**
     * Cancel (soft-delete) a pending schedule change request.
     */
    public function destroy(Request $request, ScheduleChangeRequest $scheduleChangeRequest)
    {
        $faculty = $request->user()->faculty;

        if (!$faculty) {
            return back()->withErrors(['error' => 'Unauthorized.']);
        }

        $result = $faculty->cancelScheduleChangeRequest($scheduleChangeRequest);

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['error_message']]);
        }

        return back()->with('success', 'Schedule change request cancelled.');
    }
}
