<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                     */
    /* ------------------------------------------------------------------ */

    public function faculty(): HasOne
    {
        return $this->hasOne(Faculty::class);
    }

    public function createdSchedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'created_by');
    }

    public function importBatches(): HasMany
    {
        return $this->hasMany(ImportBatch::class, 'imported_by');
    }

    public function attendanceAdjustments(): HasMany
    {
        return $this->hasMany(AttendanceAdjustment::class, 'adjusted_by');
    }

    public function approvedDtrRecords(): HasMany
    {
        return $this->hasMany(DtrRecord::class, 'approved_by');
    }

    public function reviewedLeaveApplications(): HasMany
    {
        return $this->hasMany(LeaveApplication::class, 'reviewed_by');
    }

    public function updatedSystemSettings(): HasMany
    {
        return $this->hasMany(SystemSetting::class, 'updated_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
