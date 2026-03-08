<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateDtrPdfJob;
use App\Models\AttendanceAdjustment;
use App\Models\Faculty;
use App\Services\AttendanceToDtrService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminDtrExportController extends Controller
{
    /**
     * Return a JSON preview of the DTR data (rows + summary) for the modal.
     */
    public function preview(Request $request, AttendanceToDtrService $service): JsonResponse
    {
        $validated = $request->validate([
            'faculty_id' => ['required', 'integer', 'exists:faculties,id'],
            'month'      => ['required', 'integer', 'between:1,12'],
            'year'       => ['required', 'integer', 'between:2000,2100'],
        ]);

        $faculty = Faculty::query()
            ->with('department:id,name')
            ->findOrFail($validated['faculty_id']);

        $month = (int) $validated['month'];
        $year  = (int) $validated['year'];

        $conversion = $service->convertToDtr($faculty->id, $month, $year);
        $attendance = $conversion['attendance'] ?? [];
        $summary    = $conversion['summary'] ?? [];

        $rows = $this->buildRows($attendance, $month, $year);

        $periodLabel = Carbon::create($year, $month, 1)->format('F Y');

        return response()->json([
            'faculty' => [
                'id'         => $faculty->id,
                'full_name'  => $faculty->full_name,
                'department' => $faculty->department?->name ?? 'N/A',
            ],
            'periodLabel' => $periodLabel,
            'rows'        => $rows,
            'summary'     => $summary,
        ]);
    }

    /**
     * Dispatch a background job to generate the PDF, return a token to poll.
     */
    public function dispatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'faculty_id' => ['required', 'integer', 'exists:faculties,id'],
            'month'      => ['required', 'integer', 'between:1,12'],
            'year'       => ['required', 'integer', 'between:2000,2100'],
        ]);

        $faculty = Faculty::query()
            ->with('department:id,name')
            ->findOrFail($validated['faculty_id']);

        $token    = Str::uuid()->toString();
        $safeName = str_replace(' ', '_', strtolower(trim($faculty->full_name)));
        $fileName = "dtr_{$safeName}_{$validated['year']}_{$validated['month']}.pdf";

        GenerateDtrPdfJob::dispatch(
            (int) $validated['faculty_id'],
            (int) $validated['month'],
            (int) $validated['year'],
            $token,
            $fileName,
        );

        return response()->json([
            'token'    => $token,
            'fileName' => $fileName,
            'message'  => 'PDF generation started.',
        ]);
    }

    /**
     * Check if the PDF has been generated yet.
     */
    public function status(Request $request): JsonResponse
    {
        $token = $request->query('token');

        if (! $token) {
            return response()->json(['ready' => false], 422);
        }

        $path = "dtr-exports/{$token}.pdf";

        return response()->json([
            'ready' => Storage::disk('local')->exists($path),
        ]);
    }

    /**
     * Serve the generated PDF file for download, then clean up.
     */
    public function downloadFile(Request $request): BinaryFileResponse|JsonResponse
    {
        $token    = $request->query('token');
        $fileName = $request->query('fileName', 'dtr.pdf');

        if (! $token) {
            return response()->json(['error' => 'Missing token.'], 422);
        }

        $path = "dtr-exports/{$token}.pdf";

        if (! Storage::disk('local')->exists($path)) {
            return response()->json(['error' => 'File not ready yet.'], 404);
        }

        $fullPath = Storage::disk('local')->path($path);

        return response()
            ->download($fullPath, $fileName)
            ->deleteFileAfterSend(true);
    }

    /* ──────────────────────────────────────────────────────────────
       Helpers
       ────────────────────────────────────────────────────────────── */

    public function buildRows(array $attendance, int $month, int $year): array
    {
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $rows = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dayData = $attendance[$day] ?? ['status' => 'none', 'record' => null, 'holidays' => []];
            $record  = $dayData['record'] ?? null;

            $rows[] = [
                'day'               => $day,
                'morning_in'        => $this->timeForPeriod($record?->actual_time_in, 'morning'),
                'morning_out'       => $this->timeForPeriod($record?->actual_time_out, 'morning'),
                'afternoon_in'      => $this->timeForPeriod($record?->actual_time_in, 'afternoon'),
                'afternoon_out'     => $this->timeForPeriod($record?->actual_time_out, 'afternoon'),
                'night_in'          => $this->timeForPeriod($record?->actual_time_in, 'night'),
                'night_out'         => $this->timeForPeriod($record?->actual_time_out, 'night'),
                'tardy_minutes'     => (int) ($record?->late_minutes ?? 0),
                'undertime_minutes' => (int) ($record?->undertime_minutes ?? 0),
                'status'            => $dayData['status'] ?? 'none',
                'holiday_label'     => collect($dayData['holidays'] ?? [])->pluck('name')->filter()->implode(', '),
            ];
        }

        return $rows;
    }

    public function timeForPeriod(mixed $value, string $period): string
    {
        if (empty($value)) {
            return '';
        }

        $time = Carbon::parse($value);
        $hour = $time->hour;

        $isMorning   = $hour < 12;
        $isAfternoon = $hour >= 12 && $hour < 18;
        $isNight     = $hour >= 18;

        if (($period === 'morning' && $isMorning)
            || ($period === 'afternoon' && $isAfternoon)
            || ($period === 'night' && $isNight)) {
            return $time->format('g:iA');
        }

        return '';
    }
}
