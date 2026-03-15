<?php

namespace Database\Seeders;

use App\Models\BiometricLog;
use App\Models\ImportBatch;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BiometricLogSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $semesterStart = Carbon::parse('2026-01-06');
            $semesterEnd = Carbon::parse('2026-05-15');

            $batchIds = ImportBatch::orderBy('id')->pluck('id')->values();

            $faculties = \App\Models\Faculty::orderBy('id')->get();

            $checkInBase  = [-10, -5,  0,  2, -8, -3,  7, 12, -15, -7,  3, 15, -2,  8, -12];
            $checkOutBase = [0,  5, -2, 10,  3, -5,  7,  0,   5, -3,  2,  8,  0, -4,   6];

            foreach ($faculties as $fi => $faculty) {
                $schedule = Schedule::where('faculty_id', $faculty->id)->first();
                if (!$schedule) {
                    continue;
                }

                $scheduleDetails = ScheduleDetail::where('schedule_id', $schedule->id)->get();
                if ($scheduleDetails->isEmpty()) {
                    continue;
                }

                $detailsByDay = $scheduleDetails->keyBy('day');

                $allDates = [];
                foreach ($detailsByDay as $day => $detail) {
                    $dayNum = [
                        'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3,
                        'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 0
                    ][$day] ?? 1;

                    $current = $semesterStart->copy();
                    while ($current->lte($semesterEnd)) {
                        if ((int) $current->format('N') === $dayNum) {
                            $allDates[] = [
                                'date' => $current->format('Y-m-d'),
                                'day' => $day,
                                'detail' => $detail,
                            ];
                        }
                        $current->addDay();
                    }
                }

                usort($allDates, fn($a, $b) => $a['date'] <=> $b['date']);

                $offsetIndex = $fi % count($checkInBase);
                $totalWeeks = (int) ceil(count($allDates) / 5);

                foreach ($allDates as $wi => $dateInfo) {
                    $dateStr = $dateInfo['date'];
                    $day = $dateInfo['day'];
                    $detail = $dateInfo['detail'];

                    $batchIndex = (int) floor($wi / 6);
                    $batchId = $batchIds[$batchIndex] ?? $batchIds->last();

                    $weekLate = ($wi % 4 === 3) ? 20 : 0;
                    $inOffset = $checkInBase[$offsetIndex] + $weekLate;

                    $schedTimeIn = Carbon::parse($detail->start_time)->format('H:i:s');
                    $schedTimeOut = Carbon::parse($detail->end_time)->format('H:i:s');

                    $checkIn = Carbon::parse($dateStr . ' ' . $schedTimeIn)->addMinutes($inOffset);
                    $checkOut = Carbon::parse($dateStr . ' ' . $schedTimeOut)->addMinutes($checkOutBase[$offsetIndex]);

                    $exists = BiometricLog::where('biometric_id', $faculty->biometric_id)
                        ->where('log_datetime', $checkIn)
                        ->where('log_type', 'IN')
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    BiometricLog::create([
                        'biometric_id'    => $faculty->biometric_id,
                        'log_datetime'    => $checkIn,
                        'log_type'        => 'IN',
                        'device_id'       => 'BIOMETRIC_DEVICE_01',
                        'import_batch_id' => $batchId,
                        'is_processed'    => true,
                    ]);

                    BiometricLog::create([
                        'biometric_id'    => $faculty->biometric_id,
                        'log_datetime'    => $checkOut,
                        'log_type'        => 'OUT',
                        'device_id'       => 'BIOMETRIC_DEVICE_01',
                        'import_batch_id' => $batchId,
                        'is_processed'    => true,
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
