<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OnlineAttendanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Inertia\Inertia;

class AdminOnlineRequestController extends Controller
{
    /**
     * Display the admin online attendance approval page.
     */
    public function index(Request $request)
    {
        return Inertia::render('Admin/OnlineAttendanceApproval', [
            'requests' => OnlineAttendanceRequest::getForAdmin($request),
            'filters'  => [
                'status' => $request->query('status', ''),
                'search' => $request->query('search', ''),
            ],
            'pendingCount' => OnlineAttendanceRequest::pending()->count(),
        ]);
    }

    /**
     * AJAX endpoint: return filtered & paginated requests as JSON.
     */
    public function filter(Request $request)
    {
        return response()->json(
            OnlineAttendanceRequest::getForAdmin($request)
        );
    }

    /**
     * Approve an online attendance request.
     */
    public function approve(Request $request, OnlineAttendanceRequest $onlineRequest)
    {
        if ($onlineRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $validated = $request->validate([
            'review_remarks' => 'nullable|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($onlineRequest, $validated) {
                // Re-fetch and lock the request row to prevent concurrent/double review.
                $locked = OnlineAttendanceRequest::whereKey($onlineRequest->id)
                    ->lockForUpdate()
                    ->first();

                if (!$locked || $locked->status !== 'pending') {
                    throw new RuntimeException('This request has already been reviewed.');
                }

                $locked->update([
                    'status'         => 'approved',
                    'reviewed_by'    => Auth::id(),
                    'reviewed_at'    => now(),
                    'review_remarks' => $validated['review_remarks'] ?? null,
                ]);
            });
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Online class request approved successfully.');
    }

    /**
     * Reject an online attendance request. Remarks are required.
     */
    public function reject(Request $request, OnlineAttendanceRequest $onlineRequest)
    {
        if ($onlineRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $validated = $request->validate([
            'review_remarks' => 'required|string|min:5|max:1000',
        ]);

        try {
            DB::transaction(function () use ($onlineRequest, $validated) {
                // Re-fetch and lock the request row to prevent concurrent/double review.
                $locked = OnlineAttendanceRequest::whereKey($onlineRequest->id)
                    ->lockForUpdate()
                    ->first();

                if (!$locked || $locked->status !== 'pending') {
                    throw new RuntimeException('This request has already been reviewed.');
                }

                $locked->update([
                    'status'         => 'rejected',
                    'reviewed_by'    => Auth::id(),
                    'reviewed_at'    => now(),
                    'review_remarks' => $validated['review_remarks'],
                ]);
            });
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Online class request rejected.');
    }
}
