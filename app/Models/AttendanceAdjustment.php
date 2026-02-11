<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceAdjustment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'attendance_record_id',
        'adjustment_type',
        'original_time_in',
        'original_time_out',
        'original_status',
        'adjusted_time_in',
        'adjusted_time_out',
        'adjusted_status',
        'reason',
        'adjusted_by',
    ];

    protected function casts(): array
    {
        return [
            'original_time_in' => 'datetime',
            'original_time_out' => 'datetime',
            'adjusted_time_in' => 'datetime',
            'adjusted_time_out' => 'datetime',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                     */
    /* ------------------------------------------------------------------ */

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
