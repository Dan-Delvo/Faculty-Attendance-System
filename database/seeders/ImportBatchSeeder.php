<?php

namespace Database\Seeders;

use App\Models\ImportBatch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportBatchSeeder extends Seeder
{
    /**
     * 3 completed import batches covering the semester:
     *   Batch 1 – Weeks 1-6  (Jan 6  – Feb 10): 180 raw logs
     *   Batch 2 – Weeks 7-12 (Feb 17 – Mar 24): 180 raw logs
     *   Batch 3 – Weeks 13-18(Mar 31 – May 5) : 180 raw logs
     *
     * Each batch: 15 faculty × weeks × 2 logs (IN + OUT) = 30 per week
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $importedBy = User::where('username', 'admin')->first()?->id;

            $batches = [
                [
                    'file_name'          => 'biometric_jan06_feb10_2026.csv',
                    'file_path'          => 'imports/biometric_jan06_feb10_2026.csv',
                    'total_records'      => 180,
                    'processed_records'  => 180,
                    'failed_records'     => 0,
                    'duplicate_records'  => 0,
                    'status'             => 'completed',
                    'imported_by'        => $importedBy,
                    'started_at'         => '2026-02-11 08:00:00',
                    'completed_at'       => '2026-02-11 08:04:32',
                    'error_log'          => null,
                ],
                [
                    'file_name'          => 'biometric_feb17_mar24_2026.csv',
                    'file_path'          => 'imports/biometric_feb17_mar24_2026.csv',
                    'total_records'      => 180,
                    'processed_records'  => 180,
                    'failed_records'     => 0,
                    'duplicate_records'  => 0,
                    'status'             => 'completed',
                    'imported_by'        => $importedBy,
                    'started_at'         => '2026-03-25 08:00:00',
                    'completed_at'       => '2026-03-25 08:05:11',
                    'error_log'          => null,
                ],
                [
                    'file_name'          => 'biometric_mar31_may05_2026.csv',
                    'file_path'          => 'imports/biometric_mar31_may05_2026.csv',
                    'total_records'      => 180,
                    'processed_records'  => 180,
                    'failed_records'     => 0,
                    'duplicate_records'  => 0,
                    'status'             => 'completed',
                    'imported_by'        => $importedBy,
                    'started_at'         => '2026-05-06 08:00:00',
                    'completed_at'       => '2026-05-06 08:03:47',
                    'error_log'          => null,
                ],
            ];

            foreach ($batches as $batch) {
                ImportBatch::firstOrCreate(['file_name' => $batch['file_name']], $batch);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
