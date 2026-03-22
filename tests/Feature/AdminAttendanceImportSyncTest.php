<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AdminAttendanceImportController;
use App\Models\AttendanceRecord;
use App\Models\BiometricLog;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\ImportBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminAttendanceImportSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_reuses_unprocessed_matching_logs_from_other_batches(): void
    {
        $department = Department::factory()->create();
        $user = User::create([
            'username' => 'faculty.sync',
            'email' => 'faculty.sync@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        $faculty = Faculty::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'faculty_code' => 'FC9001',
            'biometric_id' => 'BIO-9001',
            'first_name' => 'Sync',
            'last_name' => 'Faculty',
            'is_active' => true,
        ]);

        $filePath = 'imports/biometric-logs/corrected-sync-scope.csv';
        Storage::put($filePath, implode(PHP_EOL, [
            'biometric_id,log_datetime,log_type,device_id',
            'BIO-9001,2026-03-18 08:00:00,IN,DEVICE-01',
            'BIO-9001,2026-03-18 17:00:00,OUT,DEVICE-01',
        ]));

        $oldBatch = ImportBatch::create([
            'file_name' => 'initial.csv',
            'file_path' => $filePath,
            'status' => 'pending',
            'started_at' => now(),
        ]);

        $correctedBatch = ImportBatch::create([
            'file_name' => 'corrected.csv',
            'file_path' => $filePath,
            'duplicate_records' => 1,
            'status' => 'pending',
            'started_at' => now(),
        ]);

        $timeIn = Carbon::parse('2026-03-18 08:00:00');
        $timeOut = Carbon::parse('2026-03-18 17:00:00');

        $existingUnprocessedLog = BiometricLog::create([
            'biometric_id' => $faculty->biometric_id,
            'log_datetime' => $timeIn,
            'log_type' => 'IN',
            'device_id' => 'DEVICE-01',
            'import_batch_id' => $oldBatch->id,
            'is_processed' => false,
        ]);

        $newlyImportedLog = BiometricLog::create([
            'biometric_id' => $faculty->biometric_id,
            'log_datetime' => $timeOut,
            'log_type' => 'OUT',
            'device_id' => 'DEVICE-01',
            'import_batch_id' => $correctedBatch->id,
            'is_processed' => false,
        ]);

        $response = app(AdminAttendanceImportController::class)->sync($correctedBatch->fresh());
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(2, $payload['synced_count']);
        $this->assertSame(1, $payload['attendance_records_count']);
        $this->assertTrue($existingUnprocessedLog->fresh()->is_processed);
        $this->assertTrue($newlyImportedLog->fresh()->is_processed);

        $attendanceRecord = AttendanceRecord::query()
            ->where('faculty_id', $faculty->id)
            ->whereDate('attendance_date', '2026-03-18')
            ->first();

        $this->assertNotNull($attendanceRecord);
        $this->assertSame('2026-03-18 08:00:00', $attendanceRecord->actual_time_in?->format('Y-m-d H:i:s'));
        $this->assertSame('2026-03-18 17:00:00', $attendanceRecord->actual_time_out?->format('Y-m-d H:i:s'));
        $this->assertSame('completed', $correctedBatch->fresh()->status);
        $this->assertSame('completed', $oldBatch->fresh()->status);
    }
}
