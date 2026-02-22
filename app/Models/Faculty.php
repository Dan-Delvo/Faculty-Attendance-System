<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Faculty extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'faculties';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'department_id',
        'faculty_code',
        'biometric_id',
        'first_name',
        'middle_name',
        'last_name',
        'phone',
        'employment_type',
        'date_hired',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date_hired' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                     */
    /* ------------------------------------------------------------------ */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function internalSchedules(): HasMany
    {
        return $this->hasMany(InternalSchedule::class);
    }

    public function biometricLogs(): HasMany
    {
        return $this->hasMany(BiometricLog::class, 'biometric_id', 'biometric_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function dtrRecords(): HasMany
    {
        return $this->hasMany(DtrRecord::class);
    }

    public function leaveApplications(): HasMany
    {
        return $this->hasMany(LeaveApplication::class);
    }

    /* ------------------------------------------------------------------ */
    /*  Accessors                                                          */
    /* ------------------------------------------------------------------ */

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    /* ------------------------------------------------------------------ */
    /*  Dashboard Query Methods                                           */
    /* ------------------------------------------------------------------ */

    /**
     * Get the dashboard stats cards data for this faculty.
     *
     * Returns: Total Hours This Month, Today's Classes, Attendance Rate,
     * Upcoming Events (leave applications count).
     */
    public function getDashboardStats(): array
    {
        $now = Carbon::now();

        // ── Total Hours This Month ───────────────────────────────────────
        $thisMonthHours = $this->attendanceRecords()
            ->whereMonth('attendance_date', $now->month)
            ->whereYear('attendance_date', $now->year)
            ->sum('total_hours_rendered');

        $lastMonthHours = $this->attendanceRecords()
            ->whereMonth('attendance_date', $now->copy()->subMonth()->month)
            ->whereYear('attendance_date', $now->copy()->subMonth()->year)
            ->sum('total_hours_rendered');

        $hoursDiff = round($thisMonthHours - $lastMonthHours, 1);
        $hoursChangePrefix = $hoursDiff >= 0 ? '+' : '';

        // ── Today's Classes ──────────────────────────────────────────────
        $todayName = $now->format('l'); // e.g. "Monday"
        $todayClasses = $this->getTodayScheduleDetails();
        $todayCount = count($todayClasses);

        // Find next upcoming class
        $nextClass = collect($todayClasses)
            ->first(fn($c) => $c['status'] === 'upcoming');
        $nextClassLabel = $nextClass
            ? "Next at {$nextClass['startTime']}"
            : ($todayCount > 0 ? 'All done for today' : 'No classes today');

        // ── Attendance Rate (current semester) ───────────────────────────
        $totalRecords = $this->attendanceRecords()->count();
        $presentRecords = $this->attendanceRecords()
            ->whereIn('status', ['present', 'late'])
            ->count();

        $attendanceRate = $totalRecords > 0
            ? round(($presentRecords / $totalRecords) * 100, 1)
            : 100.0;

        // ── Upcoming Events (pending leave applications) ─────────────────
        $pendingLeaves = $this->leaveApplications()
            ->where('status', 'pending')
            ->count();

        return [
            [
                'label' => 'Total Hours This Month',
                'value' => (string) round($thisMonthHours, 1),
                'unit' => 'hrs',
                'change' => "{$hoursChangePrefix}{$hoursDiff} from last month",
                'changeType' => $hoursDiff >= 0 ? 'positive' : 'negative',
                'icon' => 'clock',
            ],
            [
                'label' => "Today's Classes",
                'value' => (string) $todayCount,
                'unit' => $todayCount === 1 ? 'class' : 'classes',
                'change' => $nextClassLabel,
                'changeType' => 'neutral',
                'icon' => 'book',
            ],
            [
                'label' => 'Attendance Rate',
                'value' => (string) $attendanceRate,
                'unit' => '%',
                'change' => $totalRecords > 0
                    ? "{$presentRecords}/{$totalRecords} days present"
                    : 'No records yet',
                'changeType' => $attendanceRate >= 90 ? 'positive' : 'negative',
                'icon' => 'chart',
            ],
            [
                'label' => 'Pending Leaves',
                'value' => (string) $pendingLeaves,
                'unit' => $pendingLeaves === 1 ? 'request' : 'requests',
                'change' => $pendingLeaves > 0
                    ? 'Awaiting approval'
                    : 'No pending requests',
                'changeType' => 'neutral',
                'icon' => 'calendar',
            ],
        ];
    }

    /**
     * Get today's schedule details formatted for the dashboard.
     *
     * Each entry includes subject, code, section (from schedule_code),
     * room, start/end time, and derived status (completed / ongoing / upcoming).
     */
    public function getTodayScheduleDetails(): array
    {
        $now = Carbon::now();
        $todayName = $now->format('l');

        // Get the active schedule for this faculty
        $activeSchedule = $this->schedules()
            ->where('status', 'active')
            ->where('effective_from', '<=', $now)
            ->where('effective_until', '>=', $now)
            ->first();

        if (!$activeSchedule) {
            return [];
        }

        $details = ScheduleDetail::where('schedule_id', $activeSchedule->id)
            ->where('day_of_week', $todayName)
            ->orderBy('time_in')
            ->get();

        return $details->map(function (ScheduleDetail $detail, $index) use ($now) {
            $timeIn = Carbon::parse($detail->time_in);
            $timeOut = Carbon::parse($detail->time_out);

            // Build actual datetime for today to compare
            $todayTimeIn = $now->copy()->setTimeFrom($timeIn);
            $todayTimeOut = $now->copy()->setTimeFrom($timeOut);

            // Derive status
            if ($now->greaterThan($todayTimeOut)) {
                $status = 'completed';
            } elseif ($now->greaterThanOrEqualTo($todayTimeIn) && $now->lessThanOrEqualTo($todayTimeOut)) {
                $status = 'ongoing';
            } else {
                $status = 'upcoming';
            }

            // Extract a section-like label from schedule_code (e.g. "SCH-FAC001-2S-2026")
            $section = $detail->subject_code ?? '';

            return [
                'id' => $detail->id,
                'subject' => $detail->subject_desc ?? 'Untitled Subject',
                'code' => $detail->subject_code ?? '',
                'section' => $section,
                'room' => $detail->room ?? 'TBA',
                'startTime' => $timeIn->format('h:i A'),
                'endTime' => $timeOut->format('h:i A'),
                'status' => $status,
            ];
        })->values()->toArray();
    }

    /**
     * Get recent biometric logs formatted for the dashboard.
     *
     * Returns an array of logs ordered by most recent first, with
     * type, formatted timestamp, device, and computed status.
     */
    public function getFormattedBiometricLogs(int $limit = 20): array
    {
        $logs = $this->biometricLogs()
            ->orderBy('log_datetime', 'desc')
            ->limit($limit)
            ->get();

        if ($logs->isEmpty()) {
            return [];
        }

        // We need the schedule to determine "on-time" vs "late" vs "early-out"
        $activeSchedule = $this->schedules()
            ->where('status', 'active')
            ->first();

        // Build a lookup: day_of_week => [time_in, time_out] from schedule details
        $scheduleLookup = [];
        if ($activeSchedule) {
            $details = ScheduleDetail::where('schedule_id', $activeSchedule->id)->get();
            foreach ($details as $detail) {
                $scheduleLookup[$detail->day_of_week] = [
                    'time_in' => Carbon::parse($detail->time_in)->format('H:i'),
                    'time_out' => Carbon::parse($detail->time_out)->format('H:i'),
                ];
            }
        }

        $gracePeriodMinutes = 5;

        return $logs->map(function (BiometricLog $log) use ($scheduleLookup, $gracePeriodMinutes) {
            $logDt = Carbon::parse($log->log_datetime);
            $dayName = $logDt->format('l');
            $isCheckIn = strtoupper($log->log_type) === 'IN';

            // Determine status based on schedule
            $status = 'on-time';
            if (isset($scheduleLookup[$dayName])) {
                $sched = $scheduleLookup[$dayName];
                if ($isCheckIn) {
                    $scheduledIn = Carbon::parse($logDt->format('Y-m-d') . ' ' . $sched['time_in']);
                    if ($logDt->greaterThan($scheduledIn->copy()->addMinutes($gracePeriodMinutes))) {
                        $status = 'late';
                    }
                } else {
                    $scheduledOut = Carbon::parse($logDt->format('Y-m-d') . ' ' . $sched['time_out']);
                    if ($logDt->lessThan($scheduledOut)) {
                        $status = 'early-out';
                    }
                }
            }

            return [
                'id' => $log->id,
                'type' => $isCheckIn ? 'check-in' : 'check-out',
                'timestamp' => $logDt->format('h:i A'),
                'date' => $logDt->format('M d, Y'),
                'device' => $log->device_id ?? 'Unknown Device',
                'status' => $status,
            ];
        })->values()->toArray();
    }

    /**
     * Get monthly check-in trend data for the Recharts chart.
     *
     * Returns an array of monthly averages with check-in time in
     * minutes-since-midnight (for charting) over the last N months.
     */
    public function getCheckInTrend(int $months = 6): array
    {
        $now = Carbon::now();
        $trend = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $targetMonth = $now->copy()->subMonths($i);
            $monthLabel = $targetMonth->format('M');

            // Get all IN logs for this month
            $inLogs = $this->biometricLogs()
                ->where('log_type', 'IN')
                ->whereMonth('log_datetime', $targetMonth->month)
                ->whereYear('log_datetime', $targetMonth->year)
                ->get();

            // Get all OUT logs for this month
            $outLogs = $this->biometricLogs()
                ->where('log_type', 'OUT')
                ->whereMonth('log_datetime', $targetMonth->month)
                ->whereYear('log_datetime', $targetMonth->year)
                ->get();

            // Calculate average check-in time (minutes since midnight)
            $avgCheckIn = null;
            if ($inLogs->isNotEmpty()) {
                $totalMinutes = $inLogs->sum(function ($log) {
                    $dt = Carbon::parse($log->log_datetime);
                    return $dt->hour * 60 + $dt->minute;
                });
                $avgCheckIn = (int) round($totalMinutes / $inLogs->count());
            }

            // Calculate average check-out time (minutes since midnight)
            $avgCheckOut = null;
            if ($outLogs->isNotEmpty()) {
                $totalMinutes = $outLogs->sum(function ($log) {
                    $dt = Carbon::parse($log->log_datetime);
                    return $dt->hour * 60 + $dt->minute;
                });
                $avgCheckOut = (int) round($totalMinutes / $outLogs->count());
            }

            // Format label for tooltip
            $formatTime = function (?int $mins): string {
                if ($mins === null)
                    return '--:--';
                $h = intdiv($mins, 60);
                $m = $mins % 60;
                $ampm = $h >= 12 ? 'PM' : 'AM';
                $h12 = $h % 12 ?: 12;
                return sprintf('%02d:%02d %s', $h12, $m, $ampm);
            };

            $trend[] = [
                'month' => $monthLabel,
                'checkIn' => $avgCheckIn ?? 0,
                'checkOut' => $avgCheckOut ?? 0,
                'label' => $formatTime($avgCheckIn),
            ];
        }

        return $trend;
    }

    /**
     * Get the computed average check-in and check-out times this month.
     *
     * Returns ['avgCheckIn' => '06:55 AM', 'avgCheckOut' => '05:12 PM']
     */
    public function getMonthlyAverages(): array
    {
        $now = Carbon::now();

        $formatAvg = function (string $logType) use ($now): string {
            $logs = $this->biometricLogs()
                ->where('log_type', $logType)
                ->whereMonth('log_datetime', $now->month)
                ->whereYear('log_datetime', $now->year)
                ->get();

            if ($logs->isEmpty()) {
                return '--:--';
            }

            $totalMinutes = $logs->sum(function ($log) {
                $dt = Carbon::parse($log->log_datetime);
                return $dt->hour * 60 + $dt->minute;
            });

            $avgMinutes = (int) round($totalMinutes / $logs->count());
            $h = intdiv($avgMinutes, 60);
            $m = $avgMinutes % 60;
            $ampm = $h >= 12 ? 'PM' : 'AM';
            $h12 = $h % 12 ?: 12;

            return sprintf('%02d:%02d %s', $h12, $m, $ampm);
        };

        return [
            'avgCheckIn' => $formatAvg('IN'),
            'avgCheckOut' => $formatAvg('OUT'),
        ];
    }

    /**
     * Get the full weekly schedule for the schedule page.
     *
     * Returns an array grouped by day of week with class details.
     */
    public function getWeeklySchedule(): array
    {
        $now = Carbon::now();

        $activeSchedule = $this->schedules()
            ->where('status', 'active')
            ->where('effective_from', '<=', $now)
            ->where('effective_until', '>=', $now)
            ->first();

        if (!$activeSchedule) {
            return [];
        }

        $details = ScheduleDetail::where('schedule_id', $activeSchedule->id)
            ->orderByRaw("FIELD(day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
            ->orderBy('time_in')
            ->get();

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $schedule = [];

        foreach ($days as $day) {
            $dayDetails = $details->where('day_of_week', $day);

            if ($dayDetails->isEmpty()) {
                continue;
            }

            $schedule[] = [
                'day' => $day,
                'shortDay' => substr($day, 0, 3),
                'classes' => $dayDetails->map(function (ScheduleDetail $detail) {
                    return [
                        'id' => $detail->id,
                        'subject' => $detail->subject_desc ?? 'Untitled Subject',
                        'code' => $detail->subject_code ?? '',
                        'room' => $detail->room ?? 'TBA',
                        'startTime' => Carbon::parse($detail->time_in)->format('h:i A'),
                        'endTime' => Carbon::parse($detail->time_out)->format('h:i A'),
                        'hours' => $detail->hours_required,
                    ];
                })->values()->toArray(),
            ];
        }

        return $schedule;
    }

    /**
     * Get recent attendance records for the schedule page.
     */
    public function getRecentAttendance(int $limit = 20): array
    {
        $records = $this->attendanceRecords()
            ->with('scheduleDetail')
            ->orderBy('attendance_date', 'desc')
            ->limit($limit)
            ->get();

        return $records->map(function (AttendanceRecord $record) {
            return [
                'id' => $record->id,
                'date' => Carbon::parse($record->attendance_date)->format('M d, Y'),
                'dayOfWeek' => $record->day_of_week,
                'subject' => $record->scheduleDetail?->subject_desc ?? 'N/A',
                'subjectCode' => $record->scheduleDetail?->subject_code ?? '',
                'timeIn' => $record->actual_time_in ? Carbon::parse($record->actual_time_in)->format('h:i A') : '--:--',
                'timeOut' => $record->actual_time_out ? Carbon::parse($record->actual_time_out)->format('h:i A') : '--:--',
                'hoursRendered' => (float) $record->total_hours_rendered,
                'requiredHours' => (float) $record->required_hours,
                'lateMinutes' => $record->late_minutes,
                'undertimeMinutes' => $record->undertime_minutes,
                'status' => $record->status,
                'remarks' => $record->remarks,
            ];
        })->values()->toArray();
    }

    /* ------------------------------------------------------------------ */
    /*  Admin Dashboard Query Methods (static)                            */
    /* ------------------------------------------------------------------ */

    /**
     * Get admin dashboard stat cards: total faculty, timed-in today,
     * timed-out today, and average hours this month.
     */
    public static function getAdminDashboardStats(): array
    {
        $now = Carbon::now();
        $today = $now->toDateString();

        $totalFaculty = static::where('is_active', true)->count();

        // Faculty who have checked IN today (latest log = IN)
        $timedInCount = static::countTimedInToday();
        $timedOutCount = $totalFaculty - $timedInCount;

        // Average hours rendered this month across all faculty
        $avgHours = AttendanceRecord::whereMonth('attendance_date', $now->month)
            ->whereYear('attendance_date', $now->year)
            ->avg('total_hours_rendered');

        // Compare with last month
        $lastMonthAvg = AttendanceRecord::whereMonth('attendance_date', $now->copy()->subMonth()->month)
            ->whereYear('attendance_date', $now->copy()->subMonth()->year)
            ->avg('total_hours_rendered');

        $diff = round(($avgHours ?? 0) - ($lastMonthAvg ?? 0), 1);
        $prefix = $diff >= 0 ? '+' : '';

        return [
            [
                'label' => 'Total Faculty',
                'value' => (string) $totalFaculty,
                'unit'  => '',
                'change' => $totalFaculty > 0 ? 'Active members' : 'No faculty yet',
                'changeType' => 'neutral',
                'icon' => 'users',
            ],
            [
                'label' => 'Currently Timed In',
                'value' => (string) $timedInCount,
                'unit'  => '',
                'change' => $timedInCount > 0 ? 'Faculty on campus' : 'No one timed in',
                'changeType' => $timedInCount > 0 ? 'positive' : 'neutral',
                'icon' => 'login',
            ],
            [
                'label' => 'Currently Timed Out',
                'value' => (string) $timedOutCount,
                'unit'  => '',
                'change' => $timedOutCount > 0 ? 'Off campus' : 'All timed in',
                'changeType' => 'neutral',
                'icon' => 'logout',
            ],
            [
                'label' => 'Avg Hours / Month',
                'value' => (string) round($avgHours ?? 0, 1),
                'unit'  => 'hrs',
                'change' => "{$prefix}{$diff} from last month",
                'changeType' => $diff >= 0 ? 'positive' : 'negative',
                'icon' => 'chart',
            ],
        ];
    }

    /**
     * Subquery: for a given date, return the latest biometric_log row
     * (biometric_id, log_type, log_datetime) per faculty using MAX(id).
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private static function latestLogsSubquery(\DateTimeInterface $date): \Illuminate\Database\Query\Builder
    {
        $latestLogIds = DB::table('biometric_logs')
            ->selectRaw('MAX(id) as id')
            ->whereDate('log_datetime', $date)
            ->whereNull('deleted_at')
            ->groupBy('biometric_id');

        return DB::table('biometric_logs as bl')
            ->select('bl.biometric_id', 'bl.log_type', 'bl.log_datetime')
            ->whereIn('bl.id', $latestLogIds);
    }

    /**
     * Count how many faculty are currently timed in today
     * (their latest biometric log today is type IN).
     */
    public static function countTimedInToday(): int
    {
        $today = Carbon::today();

        return static::where('is_active', true)
            ->joinSub(static::latestLogsSubquery($today), 'latest', 'faculties.biometric_id', '=', 'latest.biometric_id')
            ->whereRaw('UPPER(latest.log_type) = ?', ['IN'])
            ->count('faculties.id');
    }

    /**
     * Get list of faculty currently timed-in today.
     */
    public static function getCurrentlyTimedIn(): array
    {
        $today = Carbon::today();

        return static::where('is_active', true)
            ->with('department')
            ->joinSub(static::latestLogsSubquery($today), 'latest', 'faculties.biometric_id', '=', 'latest.biometric_id')
            ->whereRaw('UPPER(latest.log_type) = ?', ['IN'])
            ->select('faculties.*', 'latest.log_datetime as timed_in_at')
            ->get()
            ->map(fn (Faculty $faculty) => [
                'id'         => $faculty->id,
                'name'       => $faculty->full_name,
                'department' => $faculty->department?->name ?? 'N/A',
                'timedInAt'  => Carbon::parse($faculty->timed_in_at)->format('h:i A'),
            ])
            ->toArray();
    }

    /**
     * Get list of faculty currently timed-out (not IN) today.
     */
    public static function getCurrentlyTimedOut(): array
    {
        $today = Carbon::today();

        return static::where('is_active', true)
            ->with('department')
            ->leftJoinSub(static::latestLogsSubquery($today), 'latest', 'faculties.biometric_id', '=', 'latest.biometric_id')
            ->where(function ($q) {
                // Not timed-in if no log today OR latest log is not IN
                $q->whereNull('latest.biometric_id')
                  ->orWhereRaw('UPPER(latest.log_type) != ?', ['IN']);
            })
            ->select('faculties.*', 'latest.log_datetime as last_activity_at')
            ->get()
            ->map(fn (Faculty $faculty) => [
                'id'           => $faculty->id,
                'name'         => $faculty->full_name,
                'department'   => $faculty->department?->name ?? 'N/A',
                'lastActivity' => $faculty->last_activity_at
                    ? Carbon::parse($faculty->last_activity_at)->format('h:i A')
                    : 'No activity',
            ])
            ->toArray();
    }

    /**
     * Get the weekly timed-in graph data (Monday to Friday of current week).
     *
     * Returns an array of { day, shortDay, count } for each weekday.
     */
    public static function getWeeklyTimedInGraph(): array
    {
        $now = Carbon::now();
        $startOfWeek = $now->copy()->startOfWeek(Carbon::MONDAY);

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $graph = [];

        foreach ($days as $index => $day) {
            $date = $startOfWeek->copy()->addDays($index);

            // Count distinct faculty who had at least one IN log on this day
            $count = BiometricLog::where('log_type', 'IN')
                ->whereDate('log_datetime', $date)
                ->distinct('biometric_id')
                ->count('biometric_id');

            $graph[] = [
                'day'      => $day,
                'shortDay' => substr($day, 0, 3),
                'count'    => $count,
                'date'     => $date->format('M d'),
                'isToday'  => $date->isToday(),
            ];
        }

        return $graph;
    }

    /**
     * Get active faculty list for dropdowns (ID, name, department).
     */
    public static function getActiveFacultyList(): array
    {
        return static::where('is_active', true)
            ->with('department')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(fn(Faculty $f) => [
                'id'         => $f->id,
                'name'       => $f->full_name,
                'department' => $f->department?->name ?? 'N/A',
            ])
            ->toArray();
    }
}
