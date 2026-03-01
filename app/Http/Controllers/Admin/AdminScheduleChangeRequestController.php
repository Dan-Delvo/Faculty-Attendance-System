<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduleChangeRequest;
use App\Models\ScheduleDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminScheduleChangeRequestController extends Controller
{
    /**
     * Display the admin schedule change requests management page.
     */
    public function index(Request $request)
    {
        return Inertia::render('Admin/ScheduleChangeRequests', [
            'requests' => ScheduleChangeRequest::getForAdmin($request),
            'filters'  => [
                'status' => $request->query('status', ''),
                'search' => $request->query('search', ''),
            ],
            'pendingCount' => ScheduleChangeRequest::pending()->count(),
        ]);
    }

    /**
     * AJAX endpoint: return filtered & paginated requests as JSON.
     */
    public function filter(Request $request)
    {
        return response()->json(
            ScheduleChangeRequest::getForAdmin($request)
        );
    }

    /**
     * Approve a schedule change request and apply the change to schedule_details.
     */
    public function approve(Request $request, ScheduleChangeRequest $scheduleChangeRequest)
    {
        if ($scheduleChangeRequest->status !== 'pending') {
            return back()->withErrors(['error' => 'This request has already been reviewed.']);
        }

        $validated = $request->validate([
            'review_remarks' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($scheduleChangeRequest, $validated) {
            // ── Apply the requested changes to the schedule detail ──────────
            $detail = ScheduleDetail::findOrFail($scheduleChangeRequest->schedule_detail_id);

            $timeIn  = Carbon::parse($scheduleChangeRequest->requested_time_in);
            $timeOut = Carbon::parse($scheduleChangeRequest->requested_time_out);

            // Recalculate hours_required from the new times
            $hoursRequired = max(0, round($timeOut->diffInMinutes($timeIn) / 60, 2));

            $detail->update([
                'day_of_week'    => $scheduleChangeRequest->requested_day_of_week,
                'time_in'        => $scheduleChangeRequest->requested_time_in,
                'time_out'       => $scheduleChangeRequest->requested_time_out,
                'room'           => $scheduleChangeRequest->requested_room ?? $detail->room,
                'hours_required' => $hoursRequired,
            ]);

            // ── Mark the request as approved ────────────────────────────────
            $scheduleChangeRequest->update([
                'status'         => 'approved',
                'reviewed_by'    => Auth::id(),
                'reviewed_at'    => now(),
                'review_remarks' => $validated['review_remarks'] ?? null,
            ]);
        });

        return back()->with('success', 'Schedule change request approved. The schedule has been updated.');
    }

    /**
     * Reject a schedule change request. Remarks are required.
     */
    public function reject(Request $request, ScheduleChangeRequest $scheduleChangeRequest)
    {
        if ($scheduleChangeRequest->status !== 'pending') {
            return back()->withErrors(['error' => 'This request has already been reviewed.']);
        }

        $validated = $request->validate([
            'review_remarks' => 'required|string|min:5|max:1000',
        ]);

        $scheduleChangeRequest->update([
            'status'         => 'rejected',
            'reviewed_by'    => Auth::id(),
            'reviewed_at'    => now(),
            'review_remarks' => $validated['review_remarks'],
        ]);

        return back()->with('success', 'Schedule change request has been rejected.');
    }
}
