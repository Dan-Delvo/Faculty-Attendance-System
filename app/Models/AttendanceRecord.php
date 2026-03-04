<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'faculty_id',
        'schedule_detail_id',
        'internal_schedule_id',
        'attendance_date',
        'day_of_week',
        'official_time_in',
        'official_time_out',
        'operational_day_of_week',
        'operational_time_in',
        'operational_time_out',
        'actual_time_in',
        'actual_time_out',
        'late_minutes',
        'undertime_minutes',
        'overtime_minutes',
        'night_minutes',
        'overtime_night_minutes',
        'total_hours_rendered',
        'required_hours',
        'status',
        'remarks',
        'is_manual_entry',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'official_time_in' => 'datetime',
            'official_time_out' => 'datetime',
            'operational_time_in' => 'datetime',
            'operational_time_out' => 'datetime',
            'actual_time_in' => 'datetime',
            'actual_time_out' => 'datetime',
            'late_minutes' => 'integer',
            'undertime_minutes' => 'integer',
            'overtime_minutes' => 'integer',
            'night_minutes' => 'integer',
            'overtime_night_minutes' => 'integer',
            'total_hours_rendered' => 'decimal:2',
            'required_hours' => 'decimal:2',
            'is_manual_entry' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                     */
    /* ------------------------------------------------------------------ */

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function scheduleDetail(): BelongsTo
    {
        return $this->belongsTo(ScheduleDetail::class);
    }

    public function internalSchedule(): BelongsTo
    {
        return $this->belongsTo(InternalSchedule::class);
    }

    public function attendanceAdjustments(): HasMany
    {
        return $this->hasMany(AttendanceAdjustment::class);
    }
}
