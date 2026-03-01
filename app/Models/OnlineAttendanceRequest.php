<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OnlineAttendanceRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'online_attendance';

    protected $fillable = [
        'faculty_id',
        'schedule_detail_id',
        'class_type',
        'attendance_date',
        'time_in',
        'time_out',
        'screenshot_in',
        'screenshot_out',
        'remarks',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_remarks',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'reviewed_at'     => 'datetime',
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

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /* ------------------------------------------------------------------ */
    /*  Scopes                                                            */
    /* ------------------------------------------------------------------ */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /* ------------------------------------------------------------------ */
    /*  Faculty Query: paginated own requests                             */
    /* ------------------------------------------------------------------ */

    /**
     * Get paginated online attendance requests for a specific faculty member.
     */
    public static function getForFaculty(int $facultyId, Request $request): array
    {
        $perPage = (int) $request->query('per_page', 10);
        $page    = (int) $request->query('page', 1);
        $status  = $request->query('status', '');

        $query = static::with(['scheduleDetail.schedule', 'reviewedBy'])
            ->where('faculty_id', $facultyId)
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $total = $query->count();
        $items = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $formatted = $items->map(function (self $req) {
            $detail = $req->scheduleDetail;

            return [
                'id'               => $req->id,
                'class_type'       => $req->class_type,
                'attendance_date'  => $req->attendance_date?->format('M d, Y'),
                'time_in'          => Carbon::parse($req->time_in)->format('h:i A'),
                'time_out'         => Carbon::parse($req->time_out)->format('h:i A'),
                'screenshot_in'    => $req->screenshot_in ? Storage::url($req->screenshot_in) : null,
                'screenshot_out'   => $req->screenshot_out ? Storage::url($req->screenshot_out) : null,
                'remarks'          => $req->remarks,
                'status'           => $req->status,
                'subject_code'     => $detail?->subject_code ?? null,
                'subject_desc'     => $detail?->subject_desc ?? null,
                'schedule_day'     => $detail?->day_of_week ?? null,
                'reviewed_by'      => $req->reviewedBy?->email ?? null,
                'reviewed_at'      => $req->reviewed_at?->format('M d, Y h:i A'),
                'review_remarks'   => $req->review_remarks,
                'created_at'       => $req->created_at?->format('M d, Y h:i A'),
            ];
        })->toArray();

        return [
            'data'         => $formatted,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / max($perPage, 1)),
        ];
    }
}
