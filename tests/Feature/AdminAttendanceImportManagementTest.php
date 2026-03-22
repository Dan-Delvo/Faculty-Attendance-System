<?php

namespace Tests\Feature;

use App\Models\BiometricLog;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\ImportBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminAttendanceImportManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_an_unsynced_import_batch(): void
    {
        Storage::fake('local');

        $admin = $this->createAdminUser();
        $faculty = $this->createFaculty('BIO-1001');
        $batch = ImportBatch::create([
            'file_name' => 'mistaken-upload.csv',
            'file_path' => 'imports/biometric-logs/mistaken-upload.csv',
            'status' => 'pending',
            'started_at' => now(),
        ]);

        Storage::put($batch->file_path, 'sample');

        $log = BiometricLog::create([
            'biometric_id' => $faculty->biometric_id,
            'log_datetime' => '2026-03-22 08:00:00',
            'log_type' => 'IN',
            'import_batch_id' => $batch->id,
            'is_processed' => false,
        ]);

        $this->actingAs($admin, 'admin')
            ->deleteJson(route('admin.attendance-imports.destroy', $batch))
            ->assertOk()
            ->assertJson([
                'message' => 'Import batch deleted successfully.',
            ]);

        $this->assertSoftDeleted('import_batches', ['id' => $batch->id]);
        $this->assertSoftDeleted('biometric_logs', ['id' => $log->id]);
        Storage::assertExists($batch->file_path);
    }

    public function test_admin_can_edit_and_delete_unsynced_logs(): void
    {
        $admin = $this->createAdminUser();
        $faculty = $this->createFaculty('BIO-2001');
        $batch = ImportBatch::create([
            'file_name' => 'editable.csv',
            'file_path' => 'imports/biometric-logs/editable.csv',
            'status' => 'pending',
            'started_at' => now(),
            'total_records' => 1,
            'processed_records' => 1,
        ]);

        $log = BiometricLog::create([
            'biometric_id' => $faculty->biometric_id,
            'log_datetime' => '2026-03-22 08:00:00',
            'log_type' => 'IN',
            'device_id' => 'DEVICE-01',
            'import_batch_id' => $batch->id,
            'is_processed' => false,
        ]);

        $this->actingAs($admin, 'admin')
            ->patchJson(route('admin.attendance-imports.logs.update', ['batch' => $batch->id, 'log' => $log->id]), [
                'biometric_id' => $faculty->biometric_id,
                'log_datetime' => '2026-03-22 17:15',
                'log_type' => 'OUT',
                'device_id' => 'DEVICE-02',
            ])
            ->assertOk()
            ->assertJson([
                'message' => 'Imported log updated successfully.',
            ]);

        $this->assertDatabaseHas('biometric_logs', [
            'id' => $log->id,
            'log_datetime' => '2026-03-22 17:15:00',
            'log_type' => 'OUT',
            'device_id' => 'DEVICE-02',
        ]);

        $this->actingAs($admin, 'admin')
            ->deleteJson(route('admin.attendance-imports.logs.destroy', ['batch' => $batch->id, 'log' => $log->id]))
            ->assertOk()
            ->assertJson([
                'message' => 'Imported log deleted successfully.',
            ]);

        $this->assertSoftDeleted('biometric_logs', ['id' => $log->id]);
        $this->assertSame(0, (int) $batch->fresh()->total_records);
    }

    public function test_synced_logs_and_batches_cannot_be_modified_or_deleted(): void
    {
        Storage::fake('local');

        $admin = $this->createAdminUser();
        $faculty = $this->createFaculty('BIO-3001');
        $batch = ImportBatch::create([
            'file_name' => 'synced.csv',
            'file_path' => 'imports/biometric-logs/synced.csv',
            'status' => 'completed',
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        Storage::put($batch->file_path, 'sample');

        $log = BiometricLog::create([
            'biometric_id' => $faculty->biometric_id,
            'log_datetime' => '2026-03-22 08:00:00',
            'log_type' => 'IN',
            'import_batch_id' => $batch->id,
            'is_processed' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->patchJson(route('admin.attendance-imports.logs.update', ['batch' => $batch->id, 'log' => $log->id]), [
                'biometric_id' => $faculty->biometric_id,
                'log_datetime' => '2026-03-22 08:30',
                'log_type' => 'IN',
            ])
            ->assertStatus(409);

        $this->actingAs($admin, 'admin')
            ->deleteJson(route('admin.attendance-imports.logs.destroy', ['batch' => $batch->id, 'log' => $log->id]))
            ->assertStatus(409);

        $this->actingAs($admin, 'admin')
            ->deleteJson(route('admin.attendance-imports.destroy', $batch))
            ->assertStatus(409);

        $this->assertDatabaseHas('biometric_logs', ['id' => $log->id]);
        $this->assertDatabaseHas('import_batches', ['id' => $batch->id]);
        Storage::assertExists($batch->file_path);
    }

    private function createAdminUser(): User
    {
        return User::create([
            'username' => 'admin.user',
            'email' => 'admin@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
    }

    private function createFaculty(string $biometricId): Faculty
    {
        $department = Department::factory()->create();
        $user = User::create([
            'username' => 'faculty.' . strtolower(str_replace('-', '', $biometricId)),
            'email' => strtolower($biometricId) . '@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);

        return Faculty::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'faculty_code' => 'FC' . substr(preg_replace('/\D/', '', $biometricId), -4),
            'biometric_id' => $biometricId,
            'first_name' => 'Test',
            'last_name' => 'Faculty',
            'is_active' => true,
        ]);
    }
}
