<?php

namespace Database\Factories;

use App\Models\Faculty;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttendanceRecord>
 */
class AttendanceRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $baseDate = Carbon::parse($this->faker->dateTimeThisMonth()->format('Y-m-d'));
        $officialIn = $baseDate->copy()->setTime(8, 0, 0);
        $officialOut = $baseDate->copy()->setTime(17, 0, 0);

        return [
            'faculty_id' => Faculty::factory(),
            'schedule_detail_id' => null,
            'internal_schedule_id' => null,
            'attendance_date' => $baseDate->toDateString(),
            'day_of_week' => $baseDate->format('l'),
            'official_time_in' => $officialIn,
            'official_time_out' => $officialOut,
            'operational_day_of_week' => $baseDate->format('l'),
            'operational_time_in' => $officialIn,
            'operational_time_out' => $officialOut,
            'actual_time_in' => $officialIn,
            'actual_time_out' => $officialOut,
            'late_minutes' => 0,
            'undertime_minutes' => 0,
            'overtime_minutes' => 0,
            'night_minutes' => 0,
            'overtime_night_minutes' => 0,
            'total_hours_rendered' => 8,
            'required_hours' => 8,
            'status' => 'present',
            'remarks' => '',
            'is_manual_entry' => false,
            'processed_at' => now(),
        ];
    }
}
