<?php

namespace Database\Seeders;

use App\Models\Faculty;
use App\Models\InternalSchedule;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduleSeeder extends Seeder
{
    /**
     * Per faculty:
     *   – 1 Schedule  (Semester 2, AY 2025-2026)
     *   – 3 ScheduleDetails (Monday / Wednesday / Friday)
     *   – 3 InternalSchedules (mirrors the schedule details)
     *
     * Totals: 15 schedules · 45 schedule_details · 45 internal_schedules
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $adminUser = User::where('username', 'admin')->first();

            // Subjects pool indexed by department code, then faculty index (0-2)
            $subjectPool = [
                'BSCS' => [
                    [
                        ['code' => 'CS101', 'desc' => 'Introduction to Programming',    'rooms' => ['CL101', 'CL101', 'CL101']],
                        ['code' => 'CS102', 'desc' => 'Discrete Mathematics',            'rooms' => ['CL102', 'CL102', 'CL102']],
                        ['code' => 'CS103', 'desc' => 'Computer Organization',           'rooms' => ['CL103', 'CL103', 'CL103']],
                    ],
                    [
                        ['code' => 'CS201', 'desc' => 'Data Structures',                 'rooms' => ['CL104', 'CL104', 'CL104']],
                        ['code' => 'CS202', 'desc' => 'Object-Oriented Programming',     'rooms' => ['CL105', 'CL105', 'CL105']],
                        ['code' => 'CS203', 'desc' => 'Operating Systems',               'rooms' => ['CL106', 'CL106', 'CL106']],
                    ],
                    [
                        ['code' => 'CS301', 'desc' => 'Algorithms and Complexity',       'rooms' => ['CL107', 'CL107', 'CL107']],
                        ['code' => 'CS302', 'desc' => 'Software Engineering',            'rooms' => ['CL108', 'CL108', 'CL108']],
                        ['code' => 'CS303', 'desc' => 'Computer Networks',               'rooms' => ['CL109', 'CL109', 'CL109']],
                    ],
                ],
                'BSIT' => [
                    [
                        ['code' => 'IT101', 'desc' => 'Networking Fundamentals',         'rooms' => ['NL101', 'NL101', 'NL101']],
                        ['code' => 'IT102', 'desc' => 'Introduction to Databases',       'rooms' => ['NL102', 'NL102', 'NL102']],
                        ['code' => 'IT103', 'desc' => 'Web Development Basics',          'rooms' => ['NL103', 'NL103', 'NL103']],
                    ],
                    [
                        ['code' => 'IT201', 'desc' => 'Database Management Systems',     'rooms' => ['NL104', 'NL104', 'NL104']],
                        ['code' => 'IT202', 'desc' => 'Network Security',                'rooms' => ['NL105', 'NL105', 'NL105']],
                        ['code' => 'IT203', 'desc' => 'Systems Analysis and Design',     'rooms' => ['NL106', 'NL106', 'NL106']],
                    ],
                    [
                        ['code' => 'IT301', 'desc' => 'Advanced Web Development',        'rooms' => ['NL107', 'NL107', 'NL107']],
                        ['code' => 'IT302', 'desc' => 'Cloud Computing',                 'rooms' => ['NL108', 'NL108', 'NL108']],
                        ['code' => 'IT303', 'desc' => 'IT Project Management',           'rooms' => ['NL109', 'NL109', 'NL109']],
                    ],
                ],
                'BSBA' => [
                    [
                        ['code' => 'BA101', 'desc' => 'Principles of Management',        'rooms' => ['BH101', 'BH101', 'BH101']],
                        ['code' => 'BA102', 'desc' => 'Business Communication',          'rooms' => ['BH102', 'BH102', 'BH102']],
                        ['code' => 'BA103', 'desc' => 'Microeconomics',                  'rooms' => ['BH103', 'BH103', 'BH103']],
                    ],
                    [
                        ['code' => 'BA201', 'desc' => 'Marketing Management',            'rooms' => ['BH104', 'BH104', 'BH104']],
                        ['code' => 'BA202', 'desc' => 'Human Resource Management',       'rooms' => ['BH105', 'BH105', 'BH105']],
                        ['code' => 'BA203', 'desc' => 'Business Finance',                'rooms' => ['BH106', 'BH106', 'BH106']],
                    ],
                    [
                        ['code' => 'BA301', 'desc' => 'Financial Management',            'rooms' => ['BH107', 'BH107', 'BH107']],
                        ['code' => 'BA302', 'desc' => 'Strategic Management',            'rooms' => ['BH108', 'BH108', 'BH108']],
                        ['code' => 'BA303', 'desc' => 'Entrepreneurship',                'rooms' => ['BH109', 'BH109', 'BH109']],
                    ],
                ],
                'BSED' => [
                    [
                        ['code' => 'ED101', 'desc' => 'Foundations of Education',        'rooms' => ['EL101', 'EL101', 'EL101']],
                        ['code' => 'ED102', 'desc' => 'Child and Adolescent Learning',   'rooms' => ['EL102', 'EL102', 'EL102']],
                        ['code' => 'ED103', 'desc' => 'The Teaching Profession',         'rooms' => ['EL103', 'EL103', 'EL103']],
                    ],
                    [
                        ['code' => 'ED201', 'desc' => 'Curriculum Development',          'rooms' => ['EL104', 'EL104', 'EL104']],
                        ['code' => 'ED202', 'desc' => 'Assessment in Learning',          'rooms' => ['EL105', 'EL105', 'EL105']],
                        ['code' => 'ED203', 'desc' => 'Technology in Education',         'rooms' => ['EL106', 'EL106', 'EL106']],
                    ],
                    [
                        ['code' => 'ED301', 'desc' => 'Educational Psychology',          'rooms' => ['EL107', 'EL107', 'EL107']],
                        ['code' => 'ED302', 'desc' => 'Classroom Management',            'rooms' => ['EL108', 'EL108', 'EL108']],
                        ['code' => 'ED303', 'desc' => 'Research in Education',           'rooms' => ['EL109', 'EL109', 'EL109']],
                    ],
                ],
                'BSCE' => [
                    [
                        ['code' => 'CE101', 'desc' => 'Engineering Mathematics',         'rooms' => ['EN101', 'EN101', 'EN101']],
                        ['code' => 'CE102', 'desc' => 'Engineering Drawing',             'rooms' => ['EN102', 'EN102', 'EN102']],
                        ['code' => 'CE103', 'desc' => 'Statics of Rigid Bodies',         'rooms' => ['EN103', 'EN103', 'EN103']],
                    ],
                    [
                        ['code' => 'CE201', 'desc' => 'Structural Analysis',             'rooms' => ['EN104', 'EN104', 'EN104']],
                        ['code' => 'CE202', 'desc' => 'Fluid Mechanics',                 'rooms' => ['EN105', 'EN105', 'EN105']],
                        ['code' => 'CE203', 'desc' => 'Soil Mechanics',                  'rooms' => ['EN106', 'EN106', 'EN106']],
                    ],
                    [
                        ['code' => 'CE301', 'desc' => 'Engineering Mechanics',           'rooms' => ['EN107', 'EN107', 'EN107']],
                        ['code' => 'CE302', 'desc' => 'Transportation Engineering',      'rooms' => ['EN108', 'EN108', 'EN108']],
                        ['code' => 'CE303', 'desc' => 'Construction Management',         'rooms' => ['EN109', 'EN109', 'EN109']],
                    ],
                ],
            ];

            // Schedule slots: [day_of_week, time_in (H:i), time_out (H:i), hours_required]
            $slots = [
                ['Monday',    '08:00', '11:00', 3],
                ['Wednesday', '10:00', '13:00', 3],
                ['Friday',    '14:00', '17:00', 3],
            ];

            // Track index per department to pick the right subjects
            $deptIndex = [];

            $faculties = Faculty::with('department')->get();

            foreach ($faculties as $faculty) {
                $deptCode = $faculty->department->code;

                if (!isset($deptIndex[$deptCode])) {
                    $deptIndex[$deptCode] = 0;
                }
                $fi = $deptIndex[$deptCode];               // 0, 1, or 2
                $deptIndex[$deptCode]++;

                $scheduleCode = 'SCH-' . $faculty->faculty_code . '-2S-2026';

                // Skip if already seeded
                if (Schedule::where('schedule_code', $scheduleCode)->exists()) {
                    continue;
                }

                $schedule = Schedule::create([
                    'faculty_id'     => $faculty->id,
                    'schedule_code'  => $scheduleCode,
                    'academic_year'  => 2026,
                    'semester'       => 2,
                    'effective_from' => '2026-01-06 07:00:00',
                    'effective_until' => '2026-05-15 18:00:00',
                    'status'         => 'active',
                    'schedule_type'  => 'fixed',
                    'created_by'     => $adminUser->id,
                    'notes'          => 'Semester 2 AY 2025-2026 schedule for ' . $faculty->first_name . ' ' . $faculty->last_name,
                ]);

                $subjects = $subjectPool[$deptCode][$fi];

                foreach ($slots as $slotIndex => [$day, $timeIn, $timeOut, $hours]) {
                    $subject = $subjects[$slotIndex];

                    // Store times as timestamps using an arbitrary base date
                    $timeInTs  = '2026-01-01 ' . $timeIn . ':00';
                    $timeOutTs = '2026-01-01 ' . $timeOut . ':00';

                    $detail = ScheduleDetail::create([
                        'schedule_id'   => $schedule->id,
                        'day_of_week'   => $day,
                        'time_in'       => $timeInTs,
                        'time_out'      => $timeOutTs,
                        'subject_code'  => $subject['code'],
                        'subject_desc'  => $subject['desc'],
                        'room'          => $subject['rooms'][$slotIndex],
                        'hours_required' => $hours,
                    ]);

                    InternalSchedule::create([
                        'schedule_id'    => $schedule->id,
                        'faculty_id'     => $faculty->id,
                        'day_of_week'    => $day,
                        'device_time_in' => $timeInTs,
                        'device_time_out' => $timeOutTs,
                        'is_operational' => true,
                        'required_hours' => $hours,
                        'sync_status'    => 'synced',
                        'synced_at'      => now(),
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
