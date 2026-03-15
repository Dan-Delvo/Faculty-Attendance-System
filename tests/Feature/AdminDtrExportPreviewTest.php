<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Faculty;
use App\Models\Holiday;
use App\Models\User;
use App\Jobs\GenerateDtrBatchZipJob;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class AdminDtrExportPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_marks_holiday_with_attendance_and_totals_hours(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'admin');

        $faculty = Faculty::factory()->create();
        $date = Carbon::create(2026, 3, 5, 8, 0, 0);

        Holiday::factory()->create([
            'holiday_date' => $date->toDateString(),
            'name' => 'Founders Day',
            'is_recurring' => false,
        ]);

        AttendanceRecord::factory()->create([
            'faculty_id' => $faculty->id,
            'attendance_date' => $date->toDateString(),
            'day_of_week' => $date->format('l'),
            'official_time_in' => $date->copy()->setTime(8, 0, 0),
            'official_time_out' => $date->copy()->setTime(17, 0, 0),
            'operational_day_of_week' => $date->format('l'),
            'operational_time_in' => $date->copy()->setTime(8, 0, 0),
            'operational_time_out' => $date->copy()->setTime(17, 0, 0),
            'actual_time_in' => $date->copy()->setTime(8, 0, 0),
            'actual_time_out' => $date->copy()->setTime(17, 0, 0),
            'total_hours_rendered' => 8,
            'required_hours' => 8,
            'status' => 'present',
            'remarks' => '',
            'is_manual_entry' => false,
        ]);

        $response = $this->getJson(route('admin.dtr-export.preview', [
            'faculty_id' => $faculty->id,
            'month' => 3,
            'year' => 2026,
        ]));

        $response->assertOk();

        $payload = $response->json();
        $dayRow = collect($payload['rows'])->firstWhere('day', 5);

        $this->assertNotNull($dayRow);
        $this->assertTrue($dayRow['is_holiday']);
        $this->assertStringContainsString('Founders Day', $dayRow['holiday_label']);
        $this->assertSame('8:00AM', $dayRow['morning_in']);
        $this->assertEquals(8, $payload['summary']['totalHoursRendered']);
    }

    public function test_dispatch_batch_queues_job(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'admin');

        $faculties = Faculty::factory()->count(2)->create();

        Bus::fake();

        $response = $this->postJson(route('admin.dtr-export.dispatch-batch'), [
            'faculty_ids' => $faculties->pluck('id')->all(),
            'month' => 3,
            'year' => 2026,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'fileName',
                'message',
            ]);

        Bus::assertDispatched(GenerateDtrBatchZipJob::class, function (GenerateDtrBatchZipJob $job) use ($faculties) {
            return $job->facultyIds === $faculties->pluck('id')->all()
                && $job->month === 3
                && $job->year === 2026;
        });
    }

    public function test_preview_batch_returns_multiple_faculty_previews(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin, 'admin');

        $faculties = Faculty::factory()->count(2)->create();

        $response = $this->postJson(route('admin.dtr-export.preview-batch'), [
            'faculty_ids' => $faculties->pluck('id')->all(),
            'month' => 3,
            'year' => 2026,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'periodLabel',
                'previews' => [
                    ['faculty', 'rows', 'summary'],
                ],
            ]);

        $payload = $response->json();
        $this->assertCount(2, $payload['previews']);
    }
}
