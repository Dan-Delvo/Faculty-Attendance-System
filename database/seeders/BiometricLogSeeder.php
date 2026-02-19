<?php

namespace Database\Seeders;

use App\Models\BiometricLog;
use App\Models\Faculty;
use App\Models\ImportBatch;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BiometricLogSeeder extends Seeder
{
    /**
     * 18 weeks × 15 faculty × 2 logs (IN + OUT) = 540 biometric entries.
     *
     * Attendance day : Monday of each week (matching the Monday ScheduleDetail).
     * Scheduled slot : 08:00 – 11:00.
     *
     * Each faculty has a characteristic check-in offset that makes the
     * data realistic:  most arrive slightly before 08:00, a few arrive on
     * time, and every 4th week everyone is ~20 min late.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            // 18 Monday dates spanning Semester 2 AY 2025-2026
            $mondays = [
                '2026-01-06',
                '2026-01-13',
                '2026-01-20',
                '2026-01-27',  // Jan (4)
                '2026-02-03',
                '2026-02-10',
                '2026-02-17',
                '2026-02-24',  // Feb (4)
                '2026-03-03',
                '2026-03-10',
                '2026-03-17',
                '2026-03-24',  // Mar (5)
                '2026-03-31',
                '2026-04-07',
                '2026-04-14',
                '2026-04-21',  // Apr (4)
                '2026-04-28',
                '2026-05-05',
            ];

            /*
         * Characteristic offsets (minutes) from the scheduled time per faculty.
         * Index matches Faculty seed order (BIO001 → index 0, … BIO015 → index 14).
         *
         *  checkIn  – deviation from 08:00 on a normal week
         *  checkOut – deviation from 11:00 always
         */
            $checkInBase  = [-10, -5,  0,  2, -8, -3,  7, 12, -15, -7,  3, 15, -2,  8, -12];
            $checkOutBase = [0,  5, -2, 10,  3, -5,  7,  0,   5, -3,  2,  8,  0, -4,   6];

            // Import batch boundaries: weeks 0-5 → batch 1, 6-11 → batch 2, 12-17 → batch 3
            $batchIds = ImportBatch::orderBy('id')->pluck('id')->values();

            $faculties = Faculty::orderBy('id')->get();

            foreach ($faculties as $fi => $faculty) {
                foreach ($mondays as $wi => $monday) {

                    // --- compute batch reference ---
                    $batchIndex = (int) floor($wi / 6);                          // 0, 1, or 2
                    $batchId    = $batchIds[$batchIndex] ?? $batchIds->last();

                    // --- check-in time ---
                    $weekLate   = ($wi % 4 === 3) ? 20 : 0;                     // every 4th week
                    $inOffset   = $checkInBase[$fi] + $weekLate;
                    $checkIn    = Carbon::parse($monday . ' 08:00:00')->addMinutes($inOffset);

                    // --- check-out time ---
                    $checkOut   = Carbon::parse($monday . ' 11:00:00')->addMinutes($checkOutBase[$fi]);

                    // Skip if record already exists (idempotent re-run)
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
