<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\BiometricLog;
use App\Models\DtrRecord;
use App\Models\Faculty;
use App\Models\InternalSchedule;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * Creates 18 AttendanceRecords per faculty (1 per week, on Monday)
     * using the check-in / check-out times from BiometricLogSeeder.
     *
     * Then creates 5 DtrRecord summaries per faculty (January – May 2026).
     *
     * Totals:
     *   AttendanceRecord :  18 × 15 = 270
     *   DtrRecord        :   5 × 15 =  75
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            // Grace-period in minutes (matches SystemSetting 'late_grace_period_minutes')
            $gracePeriod = 5;

            $adminUser = User::where('username', 'admin')->first();

            $faculties = Faculty::orderBy('id')->get();

            foreach ($faculties as $faculty) {

                /* ── Resolve scheduling references ─────────────────────────── */
                $schedule = Schedule::where('faculty_id', $faculty->id)->first();
                if (! $schedule) {
                    continue;
                }

                /** @var ScheduleDetail $mondayDetail */
                $mondayDetail = ScheduleDetail::where('schedule_id', $schedule->id)
                    ->where('day', 'Monday')
                    ->first();

                /** @var InternalSchedule $mondayInternal */
                $mondayInternal = InternalSchedule::where('faculty_id', $faculty->id)
                    ->where('schedule_id', $schedule->id)
                    ->where('day_of_week', 'Monday')
                    ->first();

                if (! $mondayDetail) {
                    continue;
                }

                // Extract clock times from schedule detail (date part is irrelevant)
                $schedTimeIn  = Carbon::parse($mondayDetail->start_time)->format('H:i:s');  // "08:00:00"
                $schedTimeOut = Carbon::parse($mondayDetail->end_time)->format('H:i:s'); // "11:00:00"

                /* ── Fetch all biometric logs for this faculty, by date ─────── */
                $logsByDate = BiometricLog::where('biometric_id', $faculty->biometric_id)
                    ->orderBy('log_datetime')
                    ->get()
                    ->groupBy(fn($log) => Carbon::parse($log->log_datetime)->format('Y-m-d'));

                /* ── Per-date attendance records ────────────────────────────── */
                $monthlyData = [];   // [month => [days_present, days_late, late_min, undertime_min, hours_rendered]]

                foreach ($logsByDate as $dateStr => $logs) {

                    $inLog  = $logs->firstWhere('log_type', 'IN');
                    $outLog = $logs->firstWhere('log_type', 'OUT');

                    if (! $inLog || ! $outLog) {
                        continue; // incomplete pair — skip
                    }

                    $attendanceDate = Carbon::parse($dateStr);
                    $dayOfWeek      = 'Monday';  // all seeded dates are Mondays

                    $officialIn  = Carbon::parse($dateStr . ' ' . $schedTimeIn);
                    $officialOut = Carbon::parse($dateStr . ' ' . $schedTimeOut);
                    $actualIn    = Carbon::parse($inLog->log_datetime);
                    $actualOut   = Carbon::parse($outLog->log_datetime);

                    // ---------- Compute metrics ----------
                    // Late = arrived after official_in + grace period; counted from official_in
                    $lateMinutes = 0;
                    if ($actualIn->gt($officialIn->copy()->addMinutes($gracePeriod))) {
                        $lateMinutes = max(0, (int) $actualIn->diffInMinutes($officialIn));
                    }

                    $undertimeMinutes = 0;
                    $overtimeMinutes  = 0;
                    if ($actualOut->lt($officialOut)) {
                        $undertimeMinutes = max(0, (int) $actualOut->diffInMinutes($officialOut));
                    } else {
                        $overtimeMinutes = max(0, (int) $officialOut->diffInMinutes($actualOut));
                    }

                    $totalHours = round($actualIn->diffInMinutes($actualOut) / 60, 2);

                    // ---------- Status ----------
                    $status = match (true) {
                        $lateMinutes > 0 && $undertimeMinutes > 0 => 'late_undertime',
                        $lateMinutes > 0                          => 'late',
                        $undertimeMinutes > 0                     => 'undertime',
                        default                                   => 'present',
                    };

                    // ---------- Remarks ----------
                    $remarks = match ($status) {
                        'late'            => "Arrived {$lateMinutes} min late.",
                        'undertime'       => "Left {$undertimeMinutes} min early.",
                        'late_undertime'  => "Arrived {$lateMinutes} min late and left {$undertimeMinutes} min early.",
                        default           => 'Regular attendance.',
                    };

                    // Skip duplicate records on re-run
                    $exists = AttendanceRecord::where('faculty_id', $faculty->id)
                        ->where('attendance_date', $dateStr)
                        ->where('schedule_detail_id', $mondayDetail->id)
                        ->exists();

                    if (! $exists) {
                        AttendanceRecord::create([
                            'faculty_id'              => $faculty->id,
                            'schedule_detail_id'      => $mondayDetail->id,
                            'internal_schedule_id'    => $mondayInternal?->id,
                            'attendance_date'         => $dateStr,
                            'day_of_week'             => $dayOfWeek,
                            'official_time_in'        => $officialIn,
                            'official_time_out'       => $officialOut,
                            'operational_day_of_week' => $dayOfWeek,
                            'operational_time_in'     => $mondayInternal
                                ? Carbon::parse($dateStr . ' ' . Carbon::parse($mondayInternal->device_time_in)->format('H:i:s'))
                                : $officialIn,
                            'operational_time_out'    => $mondayInternal
                                ? Carbon::parse($dateStr . ' ' . Carbon::parse($mondayInternal->device_time_out)->format('H:i:s'))
                                : $officialOut,
                            'actual_time_in'          => $actualIn,
                            'actual_time_out'         => $actualOut,
                            'late_minutes'            => $lateMinutes,
                            'undertime_minutes'       => $undertimeMinutes,
                            'overtime_minutes'        => $overtimeMinutes,
                            'total_hours_rendered'    => $totalHours,
                            'required_hours'          => $mondayDetail->hours_required,
                            'status'                  => $status,
                            'remarks'                 => $remarks,
                            'is_manual_entry'         => false,
                            'processed_at'            => now(),
                        ]);
                    }

                    // ---------- Accumulate monthly totals ----------
                    $month = (int) $attendanceDate->format('n');
                    if (! isset($monthlyData[$month])) {
                        $monthlyData[$month] = [
                            'days_present'  => 0,
                            'days_late'     => 0,
                            'late_minutes'  => 0,
                            'undertime_min' => 0,
                            'hours_rendered'=> 0.0,
                            'required_hours'=> 0.0,
                        ];
                    }
                    $monthlyData[$month]['days_present']   += 1;
                    $monthlyData[$month]['days_late']      += ($lateMinutes > 0 ? 1 : 0);
                    $monthlyData[$month]['late_minutes']   += $lateMinutes;
                    $monthlyData[$month]['undertime_min']  += $undertimeMinutes;
                    $monthlyData[$month]['hours_rendered'] += $totalHours;
                    $monthlyData[$month]['required_hours'] += $mondayDetail->hours_required;
                }

                /* ── DTR Records (Jan – May 2026) ───────────────────────────── */
                $dtrConfig = [
                    1 => ['status' => 'approved',  'finalized_at' => '2026-01-31 17:00:00', 'approved_at' => '2026-02-05 10:00:00'],
                    2 => ['status' => 'finalized', 'finalized_at' => now(),                  'approved_at' => null],
                    3 => ['status' => 'pending',   'finalized_at' => null,                   'approved_at' => null],
                    4 => ['status' => 'pending',   'finalized_at' => null,                   'approved_at' => null],
                    5 => ['status' => 'pending',   'finalized_at' => null,                   'approved_at' => null],
                ];

                foreach ($dtrConfig as $month => $cfg) {
                    $exists = DtrRecord::where('faculty_id', $faculty->id)
                        ->where('month', $month)
                        ->where('year', 2026)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $md = $monthlyData[$month] ?? [
                        'days_present'   => 0,
                        'days_late'      => 0,
                        'late_minutes'   => 0,
                        'undertime_min'  => 0,
                        'hours_rendered' => 0.0,
                        'required_hours' => 0.0,
                    ];

                    // Scheduled Mondays per month in Semester 2 2026
                    $scheduledDays = [1 => 4, 2 => 4, 3 => 5, 4 => 4, 5 => 1];
                    $daysAbsent    = $scheduledDays[$month] - $md['days_present'];

                    DtrRecord::create([
                        'faculty_id'                => $faculty->id,
                        'month'                     => $month,
                        'year'                      => 2026,
                        'total_days_present'        => max(0, $md['days_present']),
                        'total_days_absent'         => max(0, $daysAbsent),
                        'total_days_late'           => max(0, $md['days_late']),
                        'total_late_minutes'        => max(0, $md['late_minutes']),
                        'total_undertime_minutes'   => max(0, $md['undertime_min']),
                        'total_hours_rendered'      => max(0, round($md['hours_rendered'], 2)),
                        'total_hours_required'      => max(0, $md['required_hours']),
                        'status'                    => $cfg['status'],
                        'finalized_at'              => $cfg['finalized_at'],
                        'approved_by'               => $cfg['approved_at'] ? $adminUser?->id : null,
                        'approved_at'               => $cfg['approved_at'],
                        'pdf_path'                  => null,
                        'generated_at'              => null,
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
