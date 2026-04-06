<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternalSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'schedule_id',
        'faculty_id',
        'day_of_week',
        'device_time_in',
        'device_time_out',
        'is_operational',
        'required_hours',
        'sync_status',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'device_time_in' => 'datetime',
            'device_time_out' => 'datetime',
            'is_operational' => 'boolean',
            'required_hours' => 'decimal:2',
            'synced_at' => 'datetime',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                     */
    /* ------------------------------------------------------------------ */

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }
}
