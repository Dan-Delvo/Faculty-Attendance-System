<?php

namespace Database\Seeders;

use App\Models\AttendanceAdjustment;
use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceAdjustmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $adminUser = User::where('username', 'admin')->first();
            $hrUser = User::where('username', 'hr_staff')->first();

            $adjustments = [
                [
                    'fi'            => 0,
                    'date'          => '2026-01-06',
                    'type'          => 'time_in',
                    'original_in'  => '08:15:00',
                    'original_out' => '11:00:00',
                    'adjusted_in'  => '08:00:00',
                    'adjusted_out' => null,
                    'adjusted_status' => 'present',
                    'reason'        => 'Biometric device malfunction - adjusted to scheduled time.',
                    'reviewed_by'   => $adminUser,
                ],
                [
                    'fi'            => 2,
                    'date'          => '2026-01-13',
                    'type'          => 'both',
                    'original_in'  => '08:00:00',
                    'original_out' => '10:30:00',
                    'adjusted_in'  => null,
                    'adjusted_out' => '11:00:00',
                    'adjusted_status' => 'present',
                    'reason'        => 'Faculty stayed late to complete class - adjusted overtime.',
                    'reviewed_by'   => $hrUser,
                ],
                [
                    'fi'            => 4,
                    'date'          => '2026-01-20',
                    'type'          => 'status',
                    'original_in'  => '08:00:00',
                    'original_out' => '11:00:00',
                    'adjusted_in'  => null,
                    'adjusted_out' => null,
                    'adjusted_status' => 'present',
                    'reason'        => 'Leave was approved after attendance was processed - adjusted status.',
                    'reviewed_by'   => $adminUser,
                ],
                [
                    'fi'            => 6,
                    'date'          => '2026-02-03',
                    'type'          => 'time_out',
                    'original_in'  => '08:00:00',
                    'original_out' => '11:00:00',
                    'adjusted_in'  => null,
                    'adjusted_out' => '12:00:00',
                    'adjusted_status' => 'overtime',
                    'reason'        => 'Faculty conducted extended consultation with students.',
                    'reviewed_by'   => $hrUser,
                ],
                [
                    'fi'            => 8,
                    'date'          => '2026-02-10',
                    'type'          => 'time_in',
                    'original_in'  => '08:25:00',
                    'original_out' => '11:00:00',
                    'adjusted_in'  => '08:00:00',
                    'adjusted_out' => null,
                    'adjusted_status' => 'present',
                    'reason'        => 'Traffic accident caused delay - verified with department head.',
                    'reviewed_by'   => $adminUser,
                ],
            ];

            $facultyIds = \App\Models\Faculty::pluck('id')->values();

            foreach ($adjustments as $adj) {
                $facultyId = $facultyIds[$adj['fi']] ?? null;
                if (!$facultyId) {
                    continue;
                }

                $attendance = AttendanceRecord::where('faculty_id', $facultyId)
                    ->where('attendance_date', $adj['date'])
                    ->first();

                if (!$attendance) {
                    continue;
                }

                AttendanceAdjustment::firstOrCreate(
                    [
                        'attendance_record_id' => $attendance->id,
                        'adjustment_type'     => $adj['type'],
                    ],
                    [
                        'attendance_record_id' => $attendance->id,
                        'adjustment_type'      => $adj['type'],
                        'original_time_in'     => Carbon::parse($adj['date'] . ' ' . $adj['original_in']),
                        'original_time_out'    => Carbon::parse($adj['date'] . ' ' . $adj['original_out']),
                        'original_status'      => 'late',
                        'adjusted_time_in'     => $adj['adjusted_in'] ? Carbon::parse($adj['date'] . ' ' . $adj['adjusted_in']) : null,
                        'adjusted_time_out'    => $adj['adjusted_out'] ? Carbon::parse($adj['date'] . ' ' . $adj['adjusted_out']) : null,
                        'adjusted_status'      => $adj['adjusted_status'],
                        'reason'               => $adj['reason'],
                        'adjusted_by'          => $adj['reviewed_by']?->id,
                    ]
                );
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
