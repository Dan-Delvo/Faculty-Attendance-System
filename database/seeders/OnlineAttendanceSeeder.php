<?php

namespace Database\Seeders;

use App\Models\Faculty;
use App\Models\OnlineAttendanceRequest;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OnlineAttendanceSeeder extends Seeder
{
    /**
     * 2-3 online attendance requests per faculty, mixed statuses.
     *
     * Uses placeholder screenshot images stored in the public disk.
     *
     * Totals: ~37 online_attendance_requests (15 faculties)
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $adminUser = User::where('username', 'admin')->first();
            $faculties = Faculty::orderBy('id')->get();

            // Create a 1×1 placeholder PNG for screenshot fields
            $this->ensurePlaceholderImage();

            $remarks = [
                'Conducted online lecture via Google Meet.',
                'Asynchronous module uploaded to LMS.',
                'Zoom meeting recorded — link shared with students.',
                'Online quiz and discussion board activity.',
                'Synchronous class with screen sharing for demo.',
                'Posted pre-recorded video lecture on Google Classroom.',
                'Live Q&A session for the midterm review.',
                'Async activity: students submitted reflection papers.',
            ];

            $reviewRemarks = [
                'Verified. Screenshot matches the class schedule.',
                'Approved. Attendance recorded.',
                'Rejected. Screenshot is unclear / does not match claimed time.',
                'Rejected. Date does not match any scheduled class.',
                'Approved per department verification.',
            ];

            $requestIndex = 0;

            foreach ($faculties as $faculty) {
                $schedule = Schedule::where('faculty_id', $faculty->id)->first();
                if (!$schedule) {
                    continue;
                }

                $details = ScheduleDetail::where('schedule_id', $schedule->id)
                    ->orderByRaw("FIELD(day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
                    ->get();

                // Copy placeholder per faculty so paths are realistic
                $screenshotDir = 'online-attendance/' . $faculty->id;
                Storage::disk('public')->makeDirectory($screenshotDir);

                // Give each faculty 2 or 3 requests (alternating)
                $count = ($faculty->id % 2 === 0) ? 3 : 2;

                for ($i = 0; $i < $count; $i++) {
                    $detail = $details->isNotEmpty() ? $details[$i % $details->count()] : null;

                    $classType = ($i % 2 === 0) ? 'synchronous' : 'asynchronous';

                    // Dates in February 2026
                    $attendanceDate = Carbon::create(2026, 2, 10)->addDays($i * 7);

                    // Derive times from schedule detail or use defaults
                    if ($detail) {
                        $timeIn  = Carbon::parse($detail->time_in)->format('H:i:s');
                        $timeOut = Carbon::parse($detail->time_out)->format('H:i:s');
                    } else {
                        $timeIn  = '09:00:00';
                        $timeOut = '12:00:00';
                    }

                    // Create unique placeholder screenshots per request
                    $ssInName  = "screenshot_in_{$requestIndex}.png";
                    $ssOutName = "screenshot_out_{$requestIndex}.png";

                    $ssInPath  = $screenshotDir . '/' . $ssInName;
                    $ssOutPath = $screenshotDir . '/' . $ssOutName;

                    Storage::disk('public')->copy('online-attendance/placeholder.png', $ssInPath);
                    Storage::disk('public')->copy('online-attendance/placeholder.png', $ssOutPath);

                    // Distribute statuses: first = pending, second = approved, third = rejected
                    $statuses = ['pending', 'approved', 'rejected'];
                    $status   = $statuses[$i % 3];

                    $data = [
                        'faculty_id'         => $faculty->id,
                        'schedule_detail_id' => $detail?->id,
                        'class_type'         => $classType,
                        'attendance_date'    => $attendanceDate->format('Y-m-d'),
                        'time_in'            => $timeIn,
                        'time_out'           => $timeOut,
                        'screenshot_in'      => $ssInPath,
                        'screenshot_out'     => $ssOutPath,
                        'remarks'            => $remarks[$requestIndex % count($remarks)],
                        'status'             => $status,
                        'created_at'         => $attendanceDate->copy()->setTime(rand(8, 10), rand(0, 59)),
                        'updated_at'         => $attendanceDate->copy()->setTime(rand(11, 16), rand(0, 59)),
                    ];

                    if ($status !== 'pending') {
                        $data['reviewed_by']    = $adminUser->id;
                        $data['reviewed_at']    = $attendanceDate->copy()->addDay()->setTime(rand(8, 16), rand(0, 59));
                        $data['review_remarks'] = $reviewRemarks[$requestIndex % count($reviewRemarks)];
                    }

                    OnlineAttendanceRequest::create($data);
                    $requestIndex++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create a tiny 1×1 solid-colour PNG placeholder if it doesn't exist.
     */
    private function ensurePlaceholderImage(): void
    {
        $path = 'online-attendance/placeholder.png';

        if (Storage::disk('public')->exists($path)) {
            return;
        }

        Storage::disk('public')->makeDirectory('online-attendance');

        // Minimal valid 1×1 red PNG (67 bytes)
        if (extension_loaded('gd')) {
            $img = imagecreatetruecolor(200, 120);
            $bg  = imagecolorallocate($img, 240, 240, 240);
            imagefill($img, 0, 0, $bg);

            $textColour = imagecolorallocate($img, 120, 120, 120);
            imagestring($img, 4, 40, 50, 'Screenshot Placeholder', $textColour);

            ob_start();
            imagepng($img);
            $binary = ob_get_clean();
            imagedestroy($img);

            Storage::disk('public')->put($path, $binary);
        } else {
            // Fallback: minimal 1×1 PNG binary
            $png = base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg=='
            );
            Storage::disk('public')->put($path, $png);
        }
    }
}
