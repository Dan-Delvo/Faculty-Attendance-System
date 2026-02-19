<?php

namespace Database\Seeders;

use App\Models\Faculty;
use App\Models\LeaveApplication;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveApplicationSeeder extends Seeder
{
    /**
     * ~15 leave applications spread across the 15 faculty members.
     * Mix of leave types and statuses (approved, rejected, pending).
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $hrUser    = User::where('username', 'hr_staff')->first();
            $adminUser = User::where('username', 'admin')->first();

            $faculties = Faculty::orderBy('id')->pluck('id')->values();

            // [faculty_index, leave_type, start_date, end_date, reason, status, reviewed_by, reviewed_at, review_remarks]
            $applications = [
                [
                    'fi'             => 0,
                    'leave_type'     => 'sick_leave',
                    'start_date'     => '2026-01-13',
                    'end_date'       => '2026-01-14',
                    'total_days'     => 2,
                    'reason'         => 'Fever and upper respiratory tract infection. Doctor advised 2 days bed rest.',
                    'status'         => 'approved',
                    'reviewed_by'    => $hrUser?->id,
                    'reviewed_at'    => '2026-01-12 15:00:00',
                    'review_remarks' => 'Approved. Please submit medical certificate upon return.',
                ],
                [
                    'fi'             => 1,
                    'leave_type'     => 'vacation_leave',
                    'start_date'     => '2026-03-09',
                    'end_date'       => '2026-03-10',
                    'total_days'     => 2,
                    'reason'         => 'Personal vacation leave. Family reunion.',
                    'status'         => 'approved',
                    'reviewed_by'    => $hrUser?->id,
                    'reviewed_at'    => '2026-03-05 10:30:00',
                    'review_remarks' => 'Approved.',
                ],
                [
                    'fi'             => 2,
                    'leave_type'     => 'emergency_leave',
                    'start_date'     => '2026-02-16',
                    'end_date'       => '2026-02-16',
                    'total_days'     => 1,
                    'reason'         => 'Family emergency — parent hospitalized.',
                    'status'         => 'approved',
                    'reviewed_by'    => $adminUser?->id,
                    'reviewed_at'    => '2026-02-16 09:00:00',
                    'review_remarks' => 'Approved due to family emergency.',
                ],
                [
                    'fi'             => 3,
                    'leave_type'     => 'sick_leave',
                    'start_date'     => '2026-01-27',
                    'end_date'       => '2026-01-27',
                    'total_days'     => 1,
                    'reason'         => 'Migraine and inability to report for duty.',
                    'status'         => 'approved',
                    'reviewed_by'    => $hrUser?->id,
                    'reviewed_at'    => '2026-01-26 16:00:00',
                    'review_remarks' => 'Approved.',
                ],
                [
                    'fi'             => 4,
                    'leave_type'     => 'vacation_leave',
                    'start_date'     => '2026-04-20',
                    'end_date'       => '2026-04-22',
                    'total_days'     => 3,
                    'reason'         => 'Planned family vacation during Holy Week extension.',
                    'status'         => 'approved',
                    'reviewed_by'    => $hrUser?->id,
                    'reviewed_at'    => '2026-04-15 11:00:00',
                    'review_remarks' => 'Approved. Ensure class coverage is arranged.',
                ],
                [
                    'fi'             => 5,
                    'leave_type'     => 'sick_leave',
                    'start_date'     => '2026-02-03',
                    'end_date'       => '2026-02-04',
                    'total_days'     => 2,
                    'reason'         => 'Flu-like symptoms; advised by physician to rest at home.',
                    'status'         => 'rejected',
                    'reviewed_by'    => $hrUser?->id,
                    'reviewed_at'    => '2026-02-02 17:00:00',
                    'review_remarks' => 'Rejected — no medical certificate submitted within 24 hours as required.',
                ],
                [
                    'fi'             => 6,
                    'leave_type'     => 'vacation_leave',
                    'start_date'     => '2026-03-23',
                    'end_date'       => '2026-03-24',
                    'total_days'     => 2,
                    'reason'         => 'Out-of-town seminar attendance (personal).',
                    'status'         => 'approved',
                    'reviewed_by'    => $adminUser?->id,
                    'reviewed_at'    => '2026-03-20 09:30:00',
                    'review_remarks' => 'Approved.',
                ],
                [
                    'fi'             => 7,
                    'leave_type'     => 'emergency_leave',
                    'start_date'     => '2026-01-20',
                    'end_date'       => '2026-01-20',
                    'total_days'     => 1,
                    'reason'         => 'Flash flood affected home; needed to supervise repairs.',
                    'status'         => 'approved',
                    'reviewed_by'    => $hrUser?->id,
                    'reviewed_at'    => '2026-01-20 07:30:00',
                    'review_remarks' => 'Approved as calamity leave.',
                ],
                [
                    'fi'             => 8,
                    'leave_type'     => 'sick_leave',
                    'start_date'     => '2026-02-24',
                    'end_date'       => '2026-02-25',
                    'total_days'     => 2,
                    'reason'         => 'Dental extraction and post-operative recovery.',
                    'status'         => 'approved',
                    'reviewed_by'    => $hrUser?->id,
                    'reviewed_at'    => '2026-02-23 14:00:00',
                    'review_remarks' => 'Approved with medical certificate on file.',
                ],
                [
                    'fi'             => 9,
                    'leave_type'     => 'paternity_leave',
                    'start_date'     => '2026-03-10',
                    'end_date'       => '2026-03-17',
                    'total_days'     => 7,
                    'reason'         => 'Wife gave birth on March 9, 2026. Filing paternity leave as per RA 8187.',
                    'status'         => 'approved',
                    'reviewed_by'    => $adminUser?->id,
                    'reviewed_at'    => '2026-03-08 09:00:00',
                    'review_remarks' => 'Approved. Congratulations!',
                ],
                [
                    'fi'             => 10,
                    'leave_type'     => 'vacation_leave',
                    'start_date'     => '2026-04-27',
                    'end_date'       => '2026-04-28',
                    'total_days'     => 2,
                    'reason'         => 'Annual recreational leave.',
                    'status'         => 'pending',
                    'reviewed_by'    => null,
                    'reviewed_at'    => null,
                    'review_remarks' => null,
                ],
                [
                    'fi'             => 11,
                    'leave_type'     => 'sick_leave',
                    'start_date'     => '2026-03-17',
                    'end_date'       => '2026-03-17',
                    'total_days'     => 1,
                    'reason'         => 'Severe dysmenorrhea; unable to report for duty.',
                    'status'         => 'approved',
                    'reviewed_by'    => $hrUser?->id,
                    'reviewed_at'    => '2026-03-16 18:00:00',
                    'review_remarks' => 'Approved.',
                ],
                [
                    'fi'             => 12,
                    'leave_type'     => 'vacation_leave',
                    'start_date'     => '2026-04-14',
                    'end_date'       => '2026-04-14',
                    'total_days'     => 1,
                    'reason'         => 'Personal errands — government transactions.',
                    'status'         => 'approved',
                    'reviewed_by'    => $hrUser?->id,
                    'reviewed_at'    => '2026-04-11 10:00:00',
                    'review_remarks' => 'Approved.',
                ],
                [
                    'fi'             => 13,
                    'leave_type'     => 'emergency_leave',
                    'start_date'     => '2026-02-10',
                    'end_date'       => '2026-02-10',
                    'total_days'     => 1,
                    'reason'         => 'Death of a close relative; attending funeral rites.',
                    'status'         => 'approved',
                    'reviewed_by'    => $adminUser?->id,
                    'reviewed_at'    => '2026-02-10 07:00:00',
                    'review_remarks' => 'Approved as bereavement leave. Condolences.',
                ],
                [
                    'fi'             => 14,
                    'leave_type'     => 'vacation_leave',
                    'start_date'     => '2026-05-04',
                    'end_date'       => '2026-05-06',
                    'total_days'     => 3,
                    'reason'         => 'End-of-semester personal vacation.',
                    'status'         => 'pending',
                    'reviewed_by'    => null,
                    'reviewed_at'    => null,
                    'review_remarks' => null,
                ],
            ];

            foreach ($applications as $app) {
                $facultyId = $faculties[$app['fi']] ?? null;
                if (! $facultyId) {
                    continue;
                }

                LeaveApplication::firstOrCreate(
                    [
                        'faculty_id'  => $facultyId,
                        'start_date'  => $app['start_date'],
                        'leave_type'  => $app['leave_type'],
                    ],
                    [
                        'faculty_id'      => $facultyId,
                        'leave_type'      => $app['leave_type'],
                        'start_date'      => $app['start_date'],
                        'end_date'        => $app['end_date'],
                        'total_days'      => $app['total_days'],
                        'reason'          => $app['reason'],
                        'status'          => $app['status'],
                        'reviewed_by'     => $app['reviewed_by'],
                        'reviewed_at'     => $app['reviewed_at'],
                        'review_remarks'  => $app['review_remarks'],
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
