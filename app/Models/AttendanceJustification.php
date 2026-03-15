<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceJustification extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'attendance_record_id',
        'faculty_id',
        'type',
        'justification',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_remarks',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function attendanceRecord(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    public function faculty(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function reviewer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
