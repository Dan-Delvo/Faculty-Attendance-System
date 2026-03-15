<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\OnlineAttendanceRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OnlineAttendanceController extends Controller
{
    /**
     * Display the faculty's online attendance requests page.
     */
    public function index(Request $request)
    {
        $faculty = $request->user()->faculty;

        if (!$faculty) {
            return Inertia::render('Faculty/OnlineAttendance', [
                'requests'        => ['data' => [], 'total' => 0, 'per_page' => 10, 'current_page' => 1, 'last_page' => 1],
                'scheduleDetails' => [],
                'filters'         => ['status' => ''],
            ]);
        }

        return Inertia::render('Faculty/OnlineAttendance', [
            'requests'        => OnlineAttendanceRequest::getForFaculty($faculty->id, $request),
            'scheduleDetails' => $faculty->getScheduleDetailsForOnlineAttendance(),
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
            OnlineAttendanceRequest::getForFaculty($faculty->id, $request)
        );
    }

    /**
     * Store a new online attendance request.
     */
    public function store(Request $request)
    {
        $faculty = $request->user()->faculty;

        if (!$faculty) {
            return back()->withErrors(['error' => 'Faculty profile not found.']);
        }

        $validated = $request->validate([
            'schedule_detail_id' => 'nullable|exists:schedule_details,id',
            'class_type'         => 'required|in:synchronous,asynchronous',
            'attendance_date'    => 'required|date|before_or_equal:today',
            'time_in'            => 'required|date_format:H:i',
            'time_out'           => 'nullable|date_format:H:i|after:time_in',
            'screenshot_in'      => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'screenshot_out'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'remarks'            => 'nullable|string|max:1000',
        ]);

        // Store screenshots
        $screenshotInPath  = $request->file('screenshot_in')
            ->store("online-attendance/{$faculty->id}", 'public');
        $screenshotOutPath = $request->file('screenshot_out')
            ->store("online-attendance/{$faculty->id}", 'public');

        $result = $faculty->createOnlineAttendanceRequest($validated, $screenshotInPath, $screenshotOutPath);

        if (!$result['success']) {
            // Clean up uploaded files on failure
            \Illuminate\Support\Facades\Storage::disk('public')->delete([$screenshotInPath, $screenshotOutPath]);
            return back()->withErrors([$result['error_field'] => $result['error_message']]);
        }

        return back()->with('success', 'Online attendance request submitted successfully.');
    }

    /**
     * Cancel (soft-delete) a pending online attendance request.
     */
    public function destroy(Request $request, OnlineAttendanceRequest $onlineAttendanceRequest)
    {
        $faculty = $request->user()->faculty;

        if (!$faculty) {
            return back()->withErrors(['error' => 'Unauthorized.']);
        }

        $result = $faculty->cancelOnlineAttendanceRequest($onlineAttendanceRequest);

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['error_message']]);
        }

        return back()->with('success', 'Online attendance request cancelled.');
    }
}
