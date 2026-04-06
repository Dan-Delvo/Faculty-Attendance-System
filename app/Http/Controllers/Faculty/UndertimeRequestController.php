<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\UndertimeRequest;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class UndertimeRequestController extends Controller
{
    /**
     * Display the faculty's undertime requests page.
     */
    public function index(Request $request)
    {
        $faculty = $request->user()->faculty;

        if (!$faculty) {
            return Inertia::render('Faculty/UndertimeRequests', [
                'requests' => ['data' => [], 'total' => 0, 'per_page' => 10, 'current_page' => 1, 'last_page' => 1],
                'filters' => ['status' => ''],
            ]);
        }

        $status = $request->query('status', '');
        $query = $faculty->undertimeRequests()->where('type', 'undertime');

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $requests = $query->with('attendanceRecord', 'reviewedBy')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Add attachment URLs to each request
        $requests->through(function ($request) {
            $request->attachment_url = $request->getAttachmentUrl();
            return $request;
        });

        return Inertia::render('Faculty/UndertimeRequests', [
            'requests' => $requests,
            'filters' => [
                'status' => $status,
            ],
        ]);
    }

    /**
     * Store a new undertime request.
     */
    public function store(Request $request)
    {
        $faculty = $request->user()->faculty;

        if (!$faculty) {
            return back()->withErrors(['error' => 'Faculty profile not found.']);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $undertimeRequest = new UndertimeRequest([
            'faculty_id' => $faculty->id,
            'type' => 'undertime',
            'justification' => $validated['reason'],
            'status' => 'pending',
        ]);

        if ($request->hasFile('attachment')) {
            $undertimeRequest->attachment_path = $request->file('attachment')->store('undertime_attachments', 'public');
        }

        $undertimeRequest->save();

        return back()->with('success', 'Undertime request submitted successfully.');
    }

    /**
     * Cancel (soft-delete) a pending undertime request.
     */
    public function destroy(Request $request, UndertimeRequest $undertimeRequest)
    {
        $faculty = $request->user()->faculty;

        if (!$faculty || $undertimeRequest->faculty_id !== $faculty->id) {
            return back()->withErrors(['error' => 'Unauthorized.']);
        }

        if ($undertimeRequest->status !== 'pending') {
            return back()->withErrors(['error' => 'Can only cancel pending requests.']);
        }

        if ($undertimeRequest->attachment_path && Storage::disk('public')->exists($undertimeRequest->attachment_path)) {
            Storage::disk('public')->delete($undertimeRequest->attachment_path);
        }

        $undertimeRequest->delete();

        return back()->with('success', 'Undertime request cancelled.');
    }

    /**
     * AJAX endpoint: return filtered & paginated requests as JSON.
     */
    public function filter(Request $request)
    {
        $faculty = $request->user()->faculty;

        if (!$faculty) {
            return response()->json([
                'data' => [],
                'total' => 0,
                'per_page' => 10,
                'current_page' => 1,
                'last_page' => 1,
            ]);
        }

        $status = $request->query('status', '');
        $query = $faculty->undertimeRequests()->where('type', 'undertime');

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $requests = $query->with('attendanceRecord', 'reviewedBy')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Add attachment URLs to each request
        $requests->through(function ($request) {
            $request->attachment_url = $request->getAttachmentUrl();
            return $request;
        });

        return response()->json($requests);
    }
}
