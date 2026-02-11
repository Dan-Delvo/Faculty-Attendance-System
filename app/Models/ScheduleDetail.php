<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduleDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'schedule_id',
        'day_of_week',
        'time_in',
        'time_out',
        'subject_code',
        'subject_desc',
        'room',
        'hours_required',
    ];

    protected function casts(): array
    {
        return [
            'time_in' => 'datetime',
            'time_out' => 'datetime',
            'hours_required' => 'integer',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                     */
    /* ------------------------------------------------------------------ */

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }
}
