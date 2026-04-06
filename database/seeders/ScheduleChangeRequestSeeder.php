<?php

namespace Database\Seeders;

use App\Models\Faculty;
use App\Models\Schedule;
use App\Models\ScheduleChangeRequest;
use App\Models\ScheduleDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduleChangeRequestSeeder extends Seeder
{
    /**
     * 2-3 schedule change requests per faculty, all pending for testing.
     *
     * Totals: ~37 schedule_change_requests (15 faculties)
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $adminUser = User::where('username', 'admin')->first();
            $faculties = Faculty::orderBy('id')->get();

            $alternateRooms = ['RM201', 'RM202', 'RM303', 'RM404', 'LB101', 'LB202', 'AUD-A', 'AUD-B'];
            $alternateDays  = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

            $reasons = [
                'I have a conflicting meeting at the original schedule.',
                'Requesting room change due to equipment needs for the lecture.',
                'Need to adjust schedule for family matters on this day.',
                'The classroom is under renovation, requesting temporary room change.',
                'Requesting a day swap to accommodate a seminar I need to attend.',
                'Medical appointment conflicts with current schedule.',
                'Overlap with graduate studies class on the same day.',
                'Department activity scheduled during my class hours.',
            ];

            $requestIndex = 0;

            foreach ($faculties as $faculty) {
                $schedule = Schedule::where('faculty_id', $faculty->id)->first();
                if (!$schedule) {
                    continue;
                }

                $details = ScheduleDetail::where('schedule_id', $schedule->id)
                    ->orderByRaw("FIELD(day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
                    ->get();

                if ($details->isEmpty()) {
                    continue;
                }

                // Give each faculty 2 or 3 requests (alternating)
                $count = ($faculty->id % 2 === 0) ? 3 : 2;

                for ($i = 0; $i < $count; $i++) {
                    $detail = $details[$i % $details->count()];

                    // Pick a different day than the current one
                    $currentDay = $detail->day;
                    $availableDays = array_values(array_filter($alternateDays, fn($d) => $d !== $currentDay));
                    $requestedDay = $availableDays[$requestIndex % count($availableDays)];

                    // Shift the time by 1-2 hours
                    $originalIn  = Carbon::parse($detail->start_time);
                    $shift       = ($i % 2 === 0) ? 1 : 2;
                    $requestedIn = $originalIn->copy()->addHours($shift)->format('H:i');
                    $requestedOut = $originalIn->copy()->addHours($shift + 3)->format('H:i');

                    $room = $alternateRooms[$requestIndex % count($alternateRooms)];

                    // Keep all requests pending so admins can approve/reject during testing.
                    $status = 'pending';

                    $effectiveDate = Carbon::create(2026, 2, 16)->addDays($i * 7);

                    $data = [
                        'faculty_id'           => $faculty->id,
                        'schedule_detail_id'   => $detail->id,
                        'requested_day_of_week' => $requestedDay,
                        'requested_time_in'    => $requestedIn,
                        'requested_time_out'   => $requestedOut,
                        'requested_room'       => $room,
                        'effective_date'       => $effectiveDate->format('Y-m-d'),
                        'reason'               => $reasons[$requestIndex % count($reasons)],
                        'status'               => $status,
                        'created_at'           => $effectiveDate->copy()->subDays(5)->setTime(rand(8, 16), rand(0, 59)),
                        'updated_at'           => $effectiveDate->copy()->subDays(3)->setTime(rand(8, 16), rand(0, 59)),
                    ];

                    ScheduleChangeRequest::create($data);
                    $requestIndex++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
