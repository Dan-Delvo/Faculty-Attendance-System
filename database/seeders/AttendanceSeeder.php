<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\BiometricLog;
use App\Models\DtrRecord;
use App\Models\Faculty;
use App\Models\InternalSchedule;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $gracePeriod = (int) SystemSetting::where('setting_key', 'late_grace_period_minutes')->value('setting_value') ?? 5;

            $adminUser = User::where('username', 'admin')->first();

            $faculties = Faculty::orderBy('id')->get();

            $dayMapping = [
                'Monday'    => 1,
                'Tuesday'   => 2,
                'Wednesday' => 3,
                'Thursday'  => 4,
                'Friday'    => 5,
                'Saturday'  => 6,
                'Sunday'    => 0,
            ];

            foreach ($faculties as $faculty) {
                $schedule = Schedule::where('faculty_id', $faculty->id)->first();
                if (!$schedule) {
                    continue;
                }

                $scheduleDetails = ScheduleDetail::where('schedule_id', $schedule->id)->get();
                if ($scheduleDetails->isEmpty()) {
                    continue;
                }

                $detailsByDay = $scheduleDetails->keyBy('day');

                $logsByDate = BiometricLog::where('biometric_id', $faculty->biometric_id)
                    ->orderBy('log_datetime')
                    ->get()
                    ->groupBy(fn($log) => Carbon::parse($log->log_datetime)->format('Y-m-d'));

                $monthlyData = [];

                foreach ($logsByDate as $dateStr => $logs) {
                    $inLog  = $logs->firstWhere('log_type', 'IN');
                    $outLog = $logs->firstWhere('log_type', 'OUT');

                    if (!$inLog || !$outLog) {
                        continue;
                    }

                    $attendanceDate = Carbon::parse($dateStr);
                    $dayOfWeek = $attendanceDate->format('l');

                    $detail = $detailsByDay[$dayOfWeek] ?? null;
                    if (!$detail) {
                        continue;
                    }

                    $internalSchedule = InternalSchedule::where('faculty_id', $faculty->id)
                        ->where('schedule_id', $schedule->id)
                        ->where('day_of_week', $dayOfWeek)
                        ->first();

                    $schedTimeIn  = Carbon::parse($detail->start_time)->format('H:i:s');
                    $schedTimeOut = Carbon::parse($detail->end_time)->format('H:i:s');

                    $officialIn  = Carbon::parse($dateStr . ' ' . $schedTimeIn);
                    $officialOut = Carbon::parse($dateStr . ' ' . $schedTimeOut);
                    $actualIn    = Carbon::parse($inLog->log_datetime);
                    $actualOut   = Carbon::parse($outLog->log_datetime);

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

                    $status = match (true) {
                        $lateMinutes > 0 && $undertimeMinutes > 0 => 'late_undertime',
                        $lateMinutes > 0                          => 'late',
                        $undertimeMinutes > 0                     => 'undertime',
                        default                                   => 'present',
                    };

                    $remarks = match ($status) {
                        'late'            => "Arrived {$lateMinutes} min late.",
                        'undertime'       => "Left {$undertimeMinutes} min early.",
                        'late_undertime'  => "Arrived {$lateMinutes} min late and left {$undertimeMinutes} min early.",
                        default           => 'Regular attendance.',
                    };

                    $exists = AttendanceRecord::where('faculty_id', $faculty->id)
                        ->where('attendance_date', $dateStr)
                        ->where('schedule_detail_id', $detail->id)
                        ->exists();

                    if (!$exists) {
                        AttendanceRecord::create([
                            'faculty_id'              => $faculty->id,
                            'schedule_detail_id'      => $detail->id,
                            'internal_schedule_id'    => $internalSchedule?->id,
                            'attendance_date'         => $dateStr,
                            'day_of_week'             => $dayOfWeek,
                            'official_time_in'        => $officialIn,
                            'official_time_out'       => $officialOut,
                            'operational_day_of_week' => $dayOfWeek,
                            'operational_time_in'     => $internalSchedule
                                ? Carbon::parse($dateStr . ' ' . Carbon::parse($internalSchedule->device_time_in)->format('H:i:s'))
                                : $officialIn,
                            'operational_time_out'    => $internalSchedule
                                ? Carbon::parse($dateStr . ' ' . Carbon::parse($internalSchedule->device_time_out)->format('H:i:s'))
                                : $officialOut,
                            'actual_time_in'          => $actualIn,
                            'actual_time_out'         => $actualOut,
                            'late_minutes'            => $lateMinutes,
                            'undertime_minutes'       => $undertimeMinutes,
                            'overtime_minutes'        => $overtimeMinutes,
                            'total_hours_rendered'    => $totalHours,
                            'required_hours'         => $detail->hours_required,
                            'status'                  => $status,
                            'remarks'                 => $remarks,
                            'is_manual_entry'         => false,
                            'processed_at'            => now(),
                        ]);
                    }

                    $month = (int) $attendanceDate->format('n');
                    if (!isset($monthlyData[$month])) {
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
                    $monthlyData[$month]['required_hours'] += $detail->hours_required;
                }

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
