<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InternalSchedule;
use App\Models\ScheduleChangeRequest;
use App\Models\ScheduleDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use RuntimeException;

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
     * Approve a schedule change request and apply the change to internal_schedules only.
     */
    public function approve(Request $request, ScheduleChangeRequest $scheduleChangeRequest)
    {
        if ($scheduleChangeRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $validated = $request->validate([
            'review_remarks' => 'nullable|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($scheduleChangeRequest, $validated) {
                // Re-fetch and lock the request row to prevent concurrent/double review.
                $locked = ScheduleChangeRequest::whereKey($scheduleChangeRequest->id)
                    ->lockForUpdate()
                    ->first();

                if (! $locked || $locked->status !== 'pending') {
                    throw new RuntimeException('This request has already been reviewed.');
                }

                // ── Keep official schedule detail unchanged; use it only as anchor ──
                $detail = ScheduleDetail::findOrFail($locked->schedule_detail_id);

                // Preserve the existing date part from the stored timestamps and apply the requested times
                $existingTimeIn  = Carbon::parse($detail->time_in);
                $existingTimeOut = Carbon::parse($detail->time_out);

                $timeIn  = (clone $existingTimeIn)->setTimeFromTimeString($locked->requested_time_in);
                $timeOut = (clone $existingTimeOut)->setTimeFromTimeString($locked->requested_time_out);

                // Ensure the requested time out is after time in before proceeding
                if ($timeOut->lessThanOrEqualTo($timeIn)) {
                    throw ValidationException::withMessages([
                        'requested_time_out' => ['The requested time out must be after the requested time in.'],
                    ]);
                }

                // Recalculate required_hours from the new times (whole hours)
                $hoursRequired = max(0, intval(round($timeOut->diffInMinutes($timeIn) / 60)));

                // ── Create or update the corresponding internal schedule ─────────
                InternalSchedule::updateOrCreate(
                    [
                        'schedule_id' => $detail->schedule_id,
                        'faculty_id'  => $locked->faculty_id,
                        'day_of_week' => $locked->requested_day_of_week,
                    ],
                    [
                        'device_time_in'  => $timeIn->toDateTimeString(),
                        'device_time_out' => $timeOut->toDateTimeString(),
                        'is_operational'  => true,
                        'required_hours'  => $hoursRequired,
                        'sync_status'     => 'pending',
                        'synced_at'       => null,
                    ]
                );

                // ── Mark the request as approved ────────────────────────────────
                $locked->update([
                    'status'         => 'approved',
                    'reviewed_by'    => Auth::id(),
                    'reviewed_at'    => now(),
                    'review_remarks' => $validated['review_remarks'] ?? null,
                ]);
            });
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (ValidationException $ve) {
            return back()->withErrors($ve->errors())->withInput();
        }

        return back()->with('success', 'Schedule change request approved. Official schedule is unchanged; internal schedule has been updated.');
    }

    /**
     * Reject a schedule change request. Remarks are required.
     */
    public function reject(Request $request, ScheduleChangeRequest $scheduleChangeRequest)
    {
        if ($scheduleChangeRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $validated = $request->validate([
            'review_remarks' => 'required|string|min:5|max:1000',
        ]);

        try {
            DB::transaction(function () use ($scheduleChangeRequest, $validated) {
                // Re-fetch and lock the request row to prevent concurrent/double review.
                $locked = ScheduleChangeRequest::whereKey($scheduleChangeRequest->id)
                    ->lockForUpdate()
                    ->first();

                if (! $locked || $locked->status !== 'pending') {
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

        return back()->with('success', 'Schedule change request has been rejected.');
    }

    /**
     * AJAX endpoint for search autocomplete suggestions.
     */
    public function searchSuggestions(Request $request)
    {
        $query = $request->query('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = ScheduleChangeRequest::with(['faculty', 'scheduleDetail.schedule'])
            ->where(function ($q) use ($query) {
                $q->whereHas('faculty', function ($fq) use ($query) {
                    $fq->where('first_name', 'like', "%{$query}%")
                       ->orWhere('last_name', 'like', "%{$query}%")
                       ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"])
                       ->orWhere('faculty_code', 'like', "%{$query}%");
                })->orWhereHas('scheduleDetail.schedule', function ($sq) use ($query) {
                    $sq->where('schedule_code', 'like', "%{$query}%");
                })->orWhereHas('scheduleDetail', function ($dq) use ($query) {
                    $dq->where('subject_code', 'like', "%{$query}%");
                });
            })
            ->limit(20)
            ->get();

        $suggestions = collect();

        foreach ($results as $req) {
            $faculty = $req->faculty;
            if ($faculty) {
                $name = trim($faculty->first_name . ' ' . $faculty->last_name);
                $key  = 'f:' . strtolower($name);
                if (! $suggestions->contains('id', $key) &&
                    (stripos($name, $query) !== false || stripos($faculty->faculty_code ?? '', $query) !== false)) {
                    $suggestions->push(['id' => $key, 'label' => $name . ' (' . ($faculty->faculty_code ?? '') . ')', 'value' => $name]);
                }
            }

            $detail = $req->scheduleDetail;
            $code   = $detail?->schedule?->schedule_code;
            if ($code) {
                $key = 's:' . strtolower($code);
                if (! $suggestions->contains('id', $key) && stripos($code, $query) !== false) {
                    $suggestions->push(['id' => $key, 'label' => 'Schedule: ' . $code, 'value' => $code]);
                }
            }

            $subj = $detail?->subject_code;
            if ($subj) {
                $key = 'subj:' . strtolower($subj);
                if (! $suggestions->contains('id', $key) && stripos($subj, $query) !== false) {
                    $suggestions->push(['id' => $key, 'label' => 'Subject: ' . $subj, 'value' => $subj]);
                }
            }
        }

        return response()->json($suggestions->take(8)->values());
    }
}
