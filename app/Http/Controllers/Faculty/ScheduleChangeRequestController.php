<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\ScheduleChangeRequest;
use App\Models\ScheduleDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleChangeRequestController extends Controller
{
    /**
     * Display the faculty's schedule change requests page.
     */
    public function index(Request $request)
    {
        /** @var Faculty|null $faculty */
        $faculty = $request->user()->faculty;

        if (!$faculty) {
            return Inertia::render('Faculty/ScheduleChangeRequests', [
                'requests'        => ['data' => [], 'total' => 0, 'per_page' => 10, 'current_page' => 1, 'last_page' => 1],
                'scheduleDetails' => [],
                'filters'         => ['status' => ''],
            ]);
        }

        // Get the faculty's active schedule details for the "create" dropdown
        $scheduleDetails = $this->getScheduleDetailsForFaculty($faculty);

        return Inertia::render('Faculty/ScheduleChangeRequests', [
            'requests'        => ScheduleChangeRequest::getForFaculty($faculty->id, $request),
            'scheduleDetails' => $scheduleDetails,
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
     * AJAX endpoint: validate the form fields without persisting.
     */
    public function validate(Request $request)
    {
        $faculty = $request->user()->faculty;

        if (!$faculty) {
            return response()->json(['errors' => ['error' => 'Faculty profile not found.']], 422);
        }

        // Run Laravel validation — failed validation auto-returns 422 JSON for XHR
        $validated = $request->validate([
            'schedule_detail_id'    => 'required|exists:schedule_details,id',
            'requested_day_of_week' => 'required|string|in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',
            'requested_time_in'     => 'required|date_format:H:i',
            'requested_time_out'    => 'required|date_format:H:i|after:requested_time_in',
            'requested_room'        => 'nullable|string|max:100',
            'effective_date'        => 'required|date|after_or_equal:today',
            'reason'                => 'required|string|max:1000',
        ]);

        $errors = [];

        // Verify ownership
        $scheduleDetail = ScheduleDetail::whereHas('schedule', function ($q) use ($faculty) {
            $q->where('faculty_id', $faculty->id);
        })->find($validated['schedule_detail_id']);

        if (!$scheduleDetail) {
            $errors['schedule_detail_id'] = 'The selected schedule does not belong to you.';
        }

        // Duplicate pending check
        if ($scheduleDetail) {
            $existingPending = ScheduleChangeRequest::where('faculty_id', $faculty->id)
                ->where('schedule_detail_id', $validated['schedule_detail_id'])
                ->where('status', 'pending')
                ->exists();

            if ($existingPending) {
                $errors['schedule_detail_id'] = 'You already have a pending request for this schedule.';
            }
        }

        // Conflict checks
        $reqDay = $validated['requested_day_of_week'];
        $reqIn  = $validated['requested_time_in'];
        $reqOut = $validated['requested_time_out'];

        // 1) Against existing schedule details
        $conflictingDetail = ScheduleDetail::whereHas('schedule', function ($q) use ($faculty) {
                $q->where('faculty_id', $faculty->id)
                  ->where('status', 'active');
            })
            ->where('id', '!=', $validated['schedule_detail_id'])
            ->where('day_of_week', $reqDay)
            ->where(function ($q) use ($reqIn, $reqOut) {
                $q->whereRaw("TIME(time_in) < ?", [$reqOut])
                  ->whereRaw("TIME(time_out) > ?", [$reqIn]);
            })
            ->first();

        if ($conflictingDetail) {
            $conflictSubject = $conflictingDetail->subject_code ?? 'another class';
            $conflictTime    = Carbon::parse($conflictingDetail->time_in)->format('H:i')
                             . '–'
                             . Carbon::parse($conflictingDetail->time_out)->format('H:i');

            $errors['requested_time_in'] = "Time conflict with {$conflictSubject} ({$conflictTime}) on {$reqDay}.";
        }

        // 2) Against other pending/approved change requests
        if (!isset($errors['requested_time_in'])) {
            $conflictingRequest = ScheduleChangeRequest::where('faculty_id', $faculty->id)
                ->whereIn('status', ['pending', 'approved'])
                ->where('requested_day_of_week', $reqDay)
                ->where(function ($q) use ($reqIn, $reqOut) {
                    $q->where('requested_time_in', '<', $reqOut)
                      ->where('requested_time_out', '>', $reqIn);
                })
                ->first();

            if ($conflictingRequest) {
                $errors['requested_time_in'] = "Time conflict with another pending/approved change request ({$conflictingRequest->requested_time_in}–{$conflictingRequest->requested_time_out}) on {$reqDay}.";
            }
        }

        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }

        return response()->json(['valid' => true]);
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
            'requested_day_of_week' => 'required|string|in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',
            'requested_time_in'     => 'required|date_format:H:i',
            'requested_time_out'    => 'required|date_format:H:i|after:requested_time_in',
            'requested_room'        => 'nullable|string|max:100',
            'effective_date'        => 'required|date|after_or_equal:today',
            'reason'                => 'required|string|max:1000',
        ]);

        // Verify the schedule detail belongs to this faculty
        $scheduleDetail = ScheduleDetail::whereHas('schedule', function ($q) use ($faculty) {
            $q->where('faculty_id', $faculty->id);
        })->find($validated['schedule_detail_id']);

        if (!$scheduleDetail) {
            return back()->withErrors(['schedule_detail_id' => 'The selected schedule does not belong to you.']);
        }

        // Block duplicate pending request for the same detail
        $existingPending = ScheduleChangeRequest::where('faculty_id', $faculty->id)
            ->where('schedule_detail_id', $validated['schedule_detail_id'])
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return back()->withErrors(['schedule_detail_id' => 'You already have a pending request for this schedule.']);
        }

        // ── Conflict check: requested time must not overlap other schedules ──
        $reqDay = $validated['requested_day_of_week'];
        $reqIn  = $validated['requested_time_in'];
        $reqOut = $validated['requested_time_out'];

        // 1) Check against existing schedule details on the same day
        $conflictingDetail = ScheduleDetail::whereHas('schedule', function ($q) use ($faculty) {
                $q->where('faculty_id', $faculty->id)
                  ->where('status', 'active');
            })
            ->where('id', '!=', $validated['schedule_detail_id'])
            ->where('day_of_week', $reqDay)
            ->where(function ($q) use ($reqIn, $reqOut) {
                $q->whereRaw("TIME(time_in) < ?", [$reqOut])
                  ->whereRaw("TIME(time_out) > ?", [$reqIn]);
            })
            ->with('schedule')
            ->first();

        if ($conflictingDetail) {
            $conflictSubject = $conflictingDetail->subject_code ?? 'another class';
            $conflictTime    = Carbon::parse($conflictingDetail->time_in)->format('H:i')
                             . '–'
                             . Carbon::parse($conflictingDetail->time_out)->format('H:i');

            return back()->withErrors([
                'requested_time_in' => "Time conflict with {$conflictSubject} ({$conflictTime}) on {$reqDay}.",
            ]);
        }

        // 2) Check against other pending/approved change requests
        $conflictingRequest = ScheduleChangeRequest::where('faculty_id', $faculty->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('requested_day_of_week', $reqDay)
            ->where(function ($q) use ($reqIn, $reqOut) {
                $q->where('requested_time_in', '<', $reqOut)
                  ->where('requested_time_out', '>', $reqIn);
            })
            ->first();

        if ($conflictingRequest) {
            return back()->withErrors([
                'requested_time_in' => "Time conflict with another pending/approved change request ({$conflictingRequest->requested_time_in}–{$conflictingRequest->requested_time_out}) on {$reqDay}.",
            ]);
        }

        ScheduleChangeRequest::create([
            'faculty_id'            => $faculty->id,
            'schedule_detail_id'    => $validated['schedule_detail_id'],
            'requested_day_of_week' => $validated['requested_day_of_week'],
            'requested_time_in'     => $validated['requested_time_in'],
            'requested_time_out'    => $validated['requested_time_out'],
            'requested_room'        => $validated['requested_room'],
            'effective_date'        => $validated['effective_date'],
            'reason'                => $validated['reason'],
            'status'                => 'pending',
        ]);

        return back()->with('success', 'Schedule change request submitted successfully.');
    }

    /**
     * Cancel (soft-delete) a pending schedule change request.
     */
    public function destroy(Request $request, ScheduleChangeRequest $scheduleChangeRequest)
    {
        $faculty = $request->user()->faculty;

        if (!$faculty || $scheduleChangeRequest->faculty_id !== $faculty->id) {
            return back()->withErrors(['error' => 'Unauthorized.']);
        }

        if ($scheduleChangeRequest->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending requests can be cancelled.']);
        }

        $scheduleChangeRequest->delete();

        return back()->with('success', 'Schedule change request cancelled.');
    }

    /**
     * Get active schedule details for a faculty member (used by index).
     */
    private function getScheduleDetailsForFaculty(Faculty $faculty): array
    {
        return $faculty->schedules()
            ->where('status', 'active')
            ->with('scheduleDetails')
            ->get()
            ->flatMap(function ($schedule) {
                return $schedule->scheduleDetails->map(function (ScheduleDetail $d) use ($schedule) {
                    return [
                        'id'            => $d->id,
                        'day_of_week'   => $d->day_of_week,
                        'time_in'       => Carbon::parse($d->time_in)->format('H:i'),
                        'time_out'      => Carbon::parse($d->time_out)->format('H:i'),
                        'subject_code'  => $d->subject_code,
                        'subject_desc'  => $d->subject_desc,
                        'room'          => $d->room,
                        'schedule_code' => $schedule->schedule_code,
                    ];
                });
            })
            ->values()
            ->toArray();
    }
}
