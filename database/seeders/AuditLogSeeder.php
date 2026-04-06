<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $adminUser = User::where('username', 'admin')->first();
            $hrUser = User::where('username', 'hr_staff')->first();
            $facultyUsers = User::role('faculty')->get();

            $auditLogs = [
                [
                    'user'          => $adminUser,
                    'action'        => 'create',
                    'table_name'    => 'import_batches',
                    'record_id'     => 1,
                    'old_values'    => null,
                    'new_values'    => ['file_name' => 'biometric_jan06_feb10_2026.csv', 'total_records' => 180],
                    'ip_address'    => '192.168.1.100',
                    'created_at'    => '2026-02-11 08:00:00',
                ],
                [
                    'user'          => $adminUser,
                    'action'        => 'create',
                    'table_name'    => 'import_batches',
                    'record_id'     => 2,
                    'old_values'    => null,
                    'new_values'    => ['file_name' => 'biometric_feb17_mar24_2026.csv', 'total_records' => 180],
                    'ip_address'    => '192.168.1.100',
                    'created_at'    => '2026-03-25 08:00:00',
                ],
                [
                    'user'          => $adminUser,
                    'action'        => 'update',
                    'table_name'    => 'schedule_change_requests',
                    'record_id'     => 1,
                    'old_values'    => ['status' => 'pending'],
                    'new_values'    => ['status' => 'approved', 'reviewed_by' => $adminUser->id],
                    'ip_address'    => '192.168.1.105',
                    'created_at'    => '2026-02-13 10:30:00',
                ],
                [
                    'user'          => $hrUser,
                    'action'        => 'update',
                    'table_name'    => 'leave_applications',
                    'record_id'     => 1,
                    'old_values'    => ['status' => 'pending'],
                    'new_values'    => ['status' => 'approved', 'reviewed_by' => $hrUser->id],
                    'ip_address'    => '192.168.1.102',
                    'created_at'    => '2026-01-12 15:00:00',
                ],
                [
                    'user'          => $adminUser,
                    'action'        => 'update',
                    'table_name'    => 'online_attendance_requests',
                    'record_id'     => 1,
                    'old_values'    => ['status' => 'pending'],
                    'new_values'    => ['status' => 'approved', 'reviewed_by' => $adminUser->id],
                    'ip_address'    => '192.168.1.105',
                    'created_at'    => '2026-02-12 14:00:00',
                ],
                [
                    'user'          => $adminUser,
                    'action'        => 'update',
                    'table_name'    => 'system_settings',
                    'record_id'     => 1,
                    'old_values'    => ['setting_value' => '4'],
                    'new_values'    => ['setting_value' => '5', 'updated_by' => $adminUser->id],
                    'ip_address'    => '192.168.1.100',
                    'created_at'    => '2026-01-15 09:00:00',
                ],
                [
                    'user'          => $adminUser,
                    'action'        => 'create',
                    'table_name'    => 'attendance_adjustments',
                    'record_id'     => 1,
                    'old_values'    => null,
                    'new_values'    => ['adjustment_type' => 'time_in', 'adjusted_by' => $adminUser->id],
                    'ip_address'    => '192.168.1.105',
                    'created_at'    => '2026-01-10 11:00:00',
                ],
            ];

            foreach ($auditLogs as $log) {
                AuditLog::firstOrCreate(
                    [
                        'user_id'    => $log['user']?->id,
                        'action'     => $log['action'],
                        'table_name' => $log['table_name'],
                        'record_id'  => $log['record_id'],
                        'created_at' => $log['created_at'],
                    ],
                    [
                        'user_id'    => $log['user']?->id,
                        'action'     => $log['action'],
                        'table_name' => $log['table_name'],
                        'record_id'  => $log['record_id'],
                        'old_values' => $log['old_values'],
                        'new_values' => $log['new_values'],
                        'ip_address' => $log['ip_address'],
                        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'created_at' => $log['created_at'],
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
