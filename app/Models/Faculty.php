<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
