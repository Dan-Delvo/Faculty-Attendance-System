<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AdminScheduleController extends Controller
{
    /**
     * Display the schedule management page with server-side pagination & filtering.
     */
    public function index(Request $request)
    {
        $schedules = Schedule::getFilteredSchedules($request);
        $faculties  = Faculty::getActiveFacultyList();
        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return Inertia::render('Admin/Schedules', [
            'schedules'   => $schedules,
            'faculties'   => $faculties,
            'departments' => $departments,
            'filters'     => [
                'search'    => $request->query('search', ''),
                'status'    => $request->query('status', ''),
                'type'      => $request->query('type', ''),
                'semester'  => $request->query('semester', ''),
                'academic_year' => $request->query('academic_year', ''),
                'department'    => $request->query('department', ''),
                'per_page'  => (int) $request->query('per_page', 10),
            ],
        ]);
    }

    /**
     * Store a new schedule with its details.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'faculty_id'      => 'required|exists:faculties,id',
            'schedule_code'   => 'required|string|max:100|unique:schedules,schedule_code',
            'academic_year'   => 'required|integer|min:2020|max:2100',
            'semester'        => 'required|integer|in:1,2,3',
            'effective_from'  => 'required|date',
            'effective_until' => 'required|date|after:effective_from',
            'status'          => 'required|in:draft,active,archived',
            'schedule_type'   => 'required|in:fixed,flexible',
            'notes'           => 'nullable|string|max:1000',
            'details'         => 'required|array|min:1',
            'details.*.day'           => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'details.*.start_time'    => 'required|date_format:H:i',
            'details.*.end_time'      => 'required|date_format:H:i',
            'details.*.course_code'   => 'required|string|max:50|regex:/\S/',
            'details.*.subject_desc'  => 'nullable|string|max:255',
            'details.*.room_code'     => 'required|string|max:100|regex:/\S/',
            'details.*.hours_required' => 'required|integer|min:1|max:12',
        ]);

        $minAllowedTime = Carbon::createFromFormat('H:i', '07:00');
        $maxAllowedTime = Carbon::createFromFormat('H:i', '21:00');

        foreach ($validated['details'] as $index => $detail) {
            $timeIn = Carbon::createFromFormat('H:i', $detail['start_time']);
            $timeOut = Carbon::createFromFormat('H:i', $detail['end_time']);

            if ($timeIn->lessThan($minAllowedTime) || $timeIn->greaterThan($maxAllowedTime)) {
                return back()
                    ->withErrors([
                        "details.$index.start_time" => 'The start time must be between 07:00 AM and 09:00 PM.',
                    ])
                    ->withInput();
            }

            if ($timeOut->lessThan($minAllowedTime) || $timeOut->greaterThan($maxAllowedTime)) {
                return back()
                    ->withErrors([
                        "details.$index.end_time" => 'The end time must be between 07:00 AM and 09:00 PM.',
                    ])
                    ->withInput();
            }

            if ($timeOut->lessThanOrEqualTo($timeIn)) {
                return back()
                    ->withErrors([
                        "details.$index.end_time" => 'The end time must be after the start time.',
                    ])
                    ->withInput();
            }
        }

        $businessRuleErrors = $this->validateScheduleBusinessRules($validated);
        if (! empty($businessRuleErrors)) {
            return back()
                ->withErrors($businessRuleErrors)
                ->withInput();
        }

        try {
            DB::transaction(function () use ($validated, $request) {
                $schedule = Schedule::create([
                    'faculty_id'      => $validated['faculty_id'],
                    'schedule_code'   => $validated['schedule_code'],
                    'academic_year'   => $validated['academic_year'],
                    'semester'        => $validated['semester'],
                    'effective_from'  => $validated['effective_from'],
                    'effective_until' => $validated['effective_until'],
                    'status'          => $validated['status'],
                    'schedule_type'   => $validated['schedule_type'],
                    'notes'           => $validated['notes'] ?? null,
                    'created_by'      => Auth::id(),
                ]);

                foreach ($validated['details'] as $detail) {
                    $schedule->scheduleDetails()->create([
                        'day'            => $detail['day'],
                        'start_time'     => Carbon::createFromFormat('H:i', $detail['start_time'])->format('Y-m-d H:i:s'),
                        'end_time'       => Carbon::createFromFormat('H:i', $detail['end_time'])->format('Y-m-d H:i:s'),
                        'course_code'    => $detail['course_code'] ?? null,
                        'subject_desc'   => $detail['subject_desc'] ?? null,
                        'room_code'      => $detail['room_code'] ?? null,
                        'hours_required' => $detail['hours_required'],
                    ]);
                }
            });
        } catch (\Throwable $e) {
            return redirect()->route('admin.schedules.index')
                ->with('error', 'Failed to create schedule. Please try again.');
        }

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule created successfully.');
    }

    /**
     * Update an existing schedule.
     */
    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'faculty_id'      => 'required|exists:faculties,id',
            'schedule_code'   => 'required|string|max:100|unique:schedules,schedule_code,' . $schedule->id,
            'academic_year'   => 'required|integer|min:2020|max:2100',
            'semester'        => 'required|integer|in:1,2,3',
            'effective_from'  => 'required|date',
            'effective_until' => 'required|date|after:effective_from',
            'status'          => 'required|in:draft,active,archived',
            'schedule_type'   => 'required|in:fixed,flexible',
            'notes'           => 'nullable|string|max:1000',
            'details'         => 'required|array|min:1',
            'details.*.id'            => 'nullable|integer|min:1',
            'details.*.day'           => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'details.*.start_time'    => 'required|date_format:H:i',
            'details.*.end_time'      => 'required|date_format:H:i',
            'details.*.course_code'   => 'required|string|max:50|regex:/\S/',
            'details.*.subject_desc'  => 'nullable|string|max:255',
            'details.*.room_code'     => 'required|string|max:100|regex:/\S/',
            'details.*.hours_required' => 'required|integer|min:1|max:12',
        ]);

        $minAllowedTime = Carbon::createFromFormat('H:i', '07:00');
        $maxAllowedTime = Carbon::createFromFormat('H:i', '21:00');

        foreach ($validated['details'] as $index => $detail) {
            $timeIn = Carbon::createFromFormat('H:i', $detail['start_time']);
            $timeOut = Carbon::createFromFormat('H:i', $detail['end_time']);

            if ($timeIn->lessThan($minAllowedTime) || $timeIn->greaterThan($maxAllowedTime)) {
                return back()
                    ->withErrors([
                        "details.$index.start_time" => 'The start time must be between 07:00 AM and 09:00 PM.',
                    ])
                    ->withInput();
            }

            if ($timeOut->lessThan($minAllowedTime) || $timeOut->greaterThan($maxAllowedTime)) {
                return back()
                    ->withErrors([
                        "details.$index.end_time" => 'The end time must be between 07:00 AM and 09:00 PM.',
                    ])
                    ->withInput();
            }

            if ($timeOut->lessThanOrEqualTo($timeIn)) {
                return back()
                    ->withErrors([
                        "details.$index.end_time" => 'The end time must be after the start time.',
                    ])
                    ->withInput();
            }
        }

        $businessRuleErrors = $this->validateScheduleBusinessRules($validated, $schedule);
        if (! empty($businessRuleErrors)) {
            return back()
                ->withErrors($businessRuleErrors)
                ->withInput();
        }

        try {
            DB::transaction(function () use ($schedule, $validated) {
                $schedule->update([
                    'faculty_id'      => $validated['faculty_id'],
                    'schedule_code'   => $validated['schedule_code'],
                    'academic_year'   => $validated['academic_year'],
                    'semester'        => $validated['semester'],
                    'effective_from'  => $validated['effective_from'],
                    'effective_until' => $validated['effective_until'],
                    'status'          => $validated['status'],
                    'schedule_type'   => $validated['schedule_type'],
                    'notes'           => $validated['notes'] ?? null,
                ]);

                $existingDetails = $schedule->scheduleDetails()->get()->keyBy('id');

                $submittedDetailIds = collect($validated['details'])
                    ->pluck('id')
                    ->filter(fn ($id) => ! empty($id))
                    ->map(fn ($id) => (int) $id)
                    ->values();

                // Soft-delete removed schedule_details so related history/attendance data
                // remains intact. Unique-key collisions for previously soft-deleted rows
                // are handled below via withTrashed restore when re-adding the same entry.
                $toRemove = $existingDetails->keys()->diff($submittedDetailIds);
                if ($toRemove->isNotEmpty()) {
                    ScheduleDetail::whereIn('id', $toRemove->all())->delete();
                }

                foreach ($validated['details'] as $detail) {
                    $payload = [
                        'day'            => $detail['day'],
                        'start_time'     => Carbon::createFromFormat('H:i', $detail['start_time'])->format('Y-m-d H:i:s'),
                        'end_time'       => Carbon::createFromFormat('H:i', $detail['end_time'])->format('Y-m-d H:i:s'),
                        'course_code'    => $detail['course_code'],
                        'subject_desc'   => $detail['subject_desc'] ?? null,
                        'room_code'      => $detail['room_code'],
                        'hours_required' => $detail['hours_required'],
                    ];

                    $detailId = isset($detail['id']) ? (int) $detail['id'] : null;

                    if ($detailId && ! $existingDetails->has($detailId)) {
                        throw ValidationException::withMessages([
                            'details' => 'One or more schedule details are invalid for this schedule.',
                        ]);
                    }

                    if ($detailId && $existingDetails->has($detailId)) {
                        $existingDetails->get($detailId)->update($payload);
                        continue;
                    }

                    // When creating a new detail, check for a soft-deleted row with the
                    // same schedule/day/time so we restore+update it instead of inserting
                    // a new row that would violate the unique_schedule_detail constraint.
                    $timeIn  = Carbon::createFromFormat('H:i', $detail['start_time'])->format('H:i:s');
                    $timeOut = Carbon::createFromFormat('H:i', $detail['end_time'])->format('H:i:s');

                    $softDeleted = ScheduleDetail::onlyTrashed()
                        ->where('schedule_id', $schedule->id)
                        ->where('day', $payload['day'])
                        ->whereRaw('TIME(start_time) = ?', [$timeIn])
                        ->whereRaw('TIME(end_time) = ?', [$timeOut])
                        ->first();

                    if ($softDeleted) {
                        $softDeleted->restore();
                        $softDeleted->update($payload);
                    } else {
                        $schedule->scheduleDetails()->create($payload);
                    }
                }
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            return back()
                ->withErrors(['general' => 'Failed to update schedule. An unexpected error occurred.'])
                ->withInput();
        }

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule updated successfully.');
    }

    /**
     * Remove a schedule (soft delete).
     */
    public function destroy(Schedule $schedule)
    {
        try {
            $schedule->scheduleDetails()->delete();
            $schedule->delete();
        } catch (\Throwable $e) {
            return redirect()->route('admin.schedules.index')
                ->with('error', 'Failed to delete schedule. Please try again.');
        }

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule deleted successfully.');
    }

    /**
     * AJAX endpoint for search suggestions.
     */
    public function searchSuggestions(Request $request)
    {
        $query = $request->query('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = Schedule::getSearchSuggestions($query);

        return response()->json($suggestions);
    }

    private function validateScheduleBusinessRules(array $validated, ?Schedule $currentSchedule = null): array
    {
        $errors = [];

        $duplicateScheduleQuery = Schedule::query()
            ->where('faculty_id', $validated['faculty_id'])
            ->where('academic_year', $validated['academic_year'])
            ->where('semester', $validated['semester']);

        if ($currentSchedule) {
            $duplicateScheduleQuery->where('id', '!=', $currentSchedule->id);
        }

        if ($duplicateScheduleQuery->exists()) {
            $errors['faculty_id'] = 'A schedule already exists for this faculty member in the selected academic year and semester.';
        }

        $detailCount = count($validated['details']);

        // Check for intra-schedule time overlaps (same day, overlapping times, any room).
        // A faculty member cannot teach two classes simultaneously, regardless of room.
        for ($i = 0; $i < $detailCount; $i++) {
            for ($j = $i + 1; $j < $detailCount; $j++) {
                if ($validated['details'][$i]['day'] !== $validated['details'][$j]['day']) {
                    continue;
                }

                $iStart = Carbon::createFromFormat('H:i', $validated['details'][$i]['start_time'])->format('H:i:s');
                $iEnd   = Carbon::createFromFormat('H:i', $validated['details'][$i]['end_time'])->format('H:i:s');
                $jStart = Carbon::createFromFormat('H:i', $validated['details'][$j]['start_time'])->format('H:i:s');
                $jEnd   = Carbon::createFromFormat('H:i', $validated['details'][$j]['end_time'])->format('H:i:s');

                if ($iStart < $jEnd && $iEnd > $jStart) {
                    if (! isset($errors["details.$i.start_time"])) {
                        $errors["details.$i.start_time"] = 'Entry #' . ($i + 1) . ' overlaps with entry #' . ($j + 1) . ' on ' . $validated['details'][$i]['day'] . '.';
                    }
                    if (! isset($errors["details.$j.start_time"])) {
                        $errors["details.$j.start_time"] = 'Entry #' . ($j + 1) . ' overlaps with entry #' . ($i + 1) . ' on ' . $validated['details'][$j]['day'] . '.';
                    }
                }
            }
        }

        for ($i = 0; $i < $detailCount; $i++) {
            $currentDetail = $validated['details'][$i];
            $currentRoom = trim((string) ($currentDetail['room_code'] ?? ''));

            if ($currentRoom === '') {
                continue;
            }

            $currentStart = Carbon::createFromFormat('H:i', $currentDetail['start_time'])->format('H:i:s');
            $currentEnd = Carbon::createFromFormat('H:i', $currentDetail['end_time'])->format('H:i:s');

            for ($j = $i + 1; $j < $detailCount; $j++) {
                $compareDetail = $validated['details'][$j];
                $compareRoom = trim((string) ($compareDetail['room_code'] ?? ''));

                if ($compareRoom === '' || strcasecmp($currentRoom, $compareRoom) !== 0) {
                    continue;
                }

                if ($currentDetail['day'] !== $compareDetail['day']) {
                    continue;
                }

                $compareStart = Carbon::createFromFormat('H:i', $compareDetail['start_time'])->format('H:i:s');
                $compareEnd = Carbon::createFromFormat('H:i', $compareDetail['end_time'])->format('H:i:s');

                if ($currentStart < $compareEnd && $currentEnd > $compareStart) {
                    $errors["details.$i.room_code"] = "Room {$currentRoom} has a conflict with entry #" . ($j + 1) . ' on ' . $currentDetail['day'] . '.';
                    $errors["details.$j.room_code"] = "Room {$compareRoom} has a conflict with entry #" . ($i + 1) . ' on ' . $compareDetail['day'] . '.';
                }
            }

            if (isset($errors["details.$i.room_code"])) {
                continue;
            }

            $roomConflictQuery = ScheduleDetail::query()
                ->where('day', $currentDetail['day'])
                ->whereNotNull('room_code')
                ->whereRaw('LOWER(TRIM(room_code)) = ?', [mb_strtolower($currentRoom)])
                ->whereRaw('TIME(start_time) < ? AND TIME(end_time) > ?', [$currentEnd, $currentStart])
                ->whereHas('schedule', function ($query) use ($validated, $currentSchedule) {
                    $query->whereDate('effective_from', '<=', $validated['effective_until'])
                        ->whereDate('effective_until', '>=', $validated['effective_from']);

                    if ($currentSchedule) {
                        $query->where('id', '!=', $currentSchedule->id);
                    }
                })
                ->with('schedule')
                ->first();

            if ($roomConflictQuery) {
                $conflictingScheduleCode = $roomConflictQuery->schedule?->schedule_code ?? 'another schedule';
                $errors["details.$i.room_code"] = "Room {$currentRoom} is already occupied on {$currentDetail['day']} for the selected time range (conflict with {$conflictingScheduleCode}).";
            }
        }

        return $errors;
    }
}
