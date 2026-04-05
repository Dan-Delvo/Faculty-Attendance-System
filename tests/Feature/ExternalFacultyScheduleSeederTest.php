<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\FacultySeeder;
use Database\Seeders\ScheduleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExternalFacultyScheduleSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_map_nested_api_schedule_payload_to_existing_database_columns(): void
    {
        $this->fakeFacultySchedulesApi();
        $this->seed(DepartmentSeeder::class);

        $this->createUser('admin', 'admin@example.com');
        $this->createUser('abarquezbenjamin', 'abarquezbenjamin@example.com');
        $this->createUser('adolforodrigo', 'adolforodrigo@example.com');

        $roomA301 = Room::create([
            'flss_room_id' => 301,
            'room_code' => 'A301',
            'building_name' => 'Academic Building A',
        ]);

        Room::create([
            'flss_room_id' => 303,
            'room_code' => 'A303',
            'building_name' => 'Academic Building A',
        ]);

        $this->seed([FacultySeeder::class, ScheduleSeeder::class]);

        $faculty = Faculty::where('faculty_code', 'FA019TG2026')->firstOrFail();

        $this->assertSame(19, $faculty->external_faculty_id);
        $this->assertSame('Part-Time', $faculty->faculty_type);
        $this->assertSame(9, $faculty->assigned_units);
        $this->assertSame('BSBA', $faculty->department?->code);

        $schedule = Schedule::where('faculty_id', $faculty->id)->firstOrFail();
        $this->assertSame(19, $schedule->external_faculty_id);

        $tuesdayDetail = ScheduleDetail::where('schedule_id', $schedule->id)
            ->where('day', 'Tuesday')
            ->firstOrFail();

        $this->assertSame('Business Management Accounting', $tuesdayDetail->course_title);
        $this->assertSame('ACCO 018', $tuesdayDetail->course_code);
        $this->assertSame('Business Management Accounting', $tuesdayDetail->subject_desc);
        $this->assertSame('A301', $tuesdayDetail->room_code);
        $this->assertSame($roomA301->id, $tuesdayDetail->room_id);
        $this->assertSame(3.0, (float) $tuesdayDetail->hours_required);

        $saturdayDetail = ScheduleDetail::query()
            ->where('day', 'Saturday')
            ->firstOrFail();

        $this->assertSame('Computer Programming 2', $saturdayDetail->course_title);
        $this->assertSame('COMP 003', $saturdayDetail->course_code);
        $this->assertSame('TBA', $saturdayDetail->room_code);
        $this->assertNull($saturdayDetail->room_id);
        $this->assertSame(5.0, (float) $saturdayDetail->hours_required);
    }

    public function test_schedule_seeder_updates_existing_details_when_nested_course_details_are_present(): void
    {
        $this->fakeFacultySchedulesApi();

        $department = Department::factory()->create(['code' => 'BSBA']);
        $admin = $this->createUser('admin', 'admin@example.com');
        $facultyUser = $this->createUser('abarquezbenjamin', 'abarquezbenjamin@example.com');

        Room::create([
            'flss_room_id' => 301,
            'room_code' => 'A301',
            'building_name' => 'Academic Building A',
        ]);

        $faculty = Faculty::create([
            'external_faculty_id' => 19,
            'user_id' => $facultyUser->id,
            'department_id' => $department->id,
            'faculty_code' => 'FA019TG2026',
            'biometric_id' => 'BIOAPI019',
            'first_name' => 'Benjamin',
            'last_name' => 'Abarquez',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'faculty_id' => $faculty->id,
            'external_faculty_id' => 19,
            'schedule_code' => 'SCH-API-19-2026',
            'academic_year' => 2026,
            'semester' => 2,
            'effective_from' => '2026-01-01 00:00:00',
            'effective_until' => '2026-12-31 23:59:59',
            'status' => 'active',
            'schedule_type' => 'fixed',
            'created_by' => $admin->id,
        ]);

        ScheduleDetail::create([
            'schedule_id' => $schedule->id,
            'day' => 'Tuesday',
            'start_time' => '2026-01-01 10:30:00',
            'end_time' => '2026-01-01 13:30:00',
            'course_title' => null,
            'course_code' => null,
            'room_code' => null,
            'subject_desc' => null,
            'hours_required' => 1,
        ]);

        $this->seed(ScheduleSeeder::class);

        $detail = ScheduleDetail::where('schedule_id', $schedule->id)
            ->where('day', 'Tuesday')
            ->where('start_time', '2026-01-01 10:30:00')
            ->firstOrFail();

        $this->assertSame('Business Management Accounting', $detail->course_title);
        $this->assertSame('ACCO 018', $detail->course_code);
        $this->assertSame('Business Management Accounting', $detail->subject_desc);
        $this->assertSame('A301', $detail->room_code);
        $this->assertSame(3.0, (float) $detail->hours_required);
    }

    private function fakeFacultySchedulesApi(): void
    {
        config()->set('services.flss_backend.key', 'test-key');
        config()->set('services.flss_backend.faculty_schedules_url', 'https://example.test/api/v1/faculty-schedules');
        config()->set('services.flss_backend.skip_ssl_verification', true);

        $payload = json_decode(file_get_contents(base_path('api.example.json')), true, 512, JSON_THROW_ON_ERROR);

        Http::fake([
            'https://example.test/api/v1/faculty-schedules*' => Http::response($payload, 200),
        ]);
    }

    private function createUser(string $username, string $email): User
    {
        return User::create([
            'username' => $username,
            'email' => $email,
            'password' => 'password',
            'is_active' => true,
        ]);
    }
}
