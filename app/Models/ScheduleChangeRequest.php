<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class ScheduleChangeRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'faculty_id',
        'schedule_detail_id',
        'requested_day_of_week',
        'requested_time_in',
        'requested_time_out',
        'requested_room',
        'effective_date',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_remarks',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'reviewed_at'    => 'datetime',
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
    /*  Admin Query: paginated all requests with faculty info            */
    /* ------------------------------------------------------------------ */

    /**
     * Get paginated change requests for admin (all faculty).
     */
    public static function getForAdmin(Request $request): array
    {
        $perPage = max(1, min((int) $request->query('per_page', 10), 100));
        $page    = max(1, (int) $request->query('page', 1));
        $status  = $request->query('status', '');
        $search  = $request->query('search', '');

        $query = static::with(['faculty.user', 'faculty.department', 'scheduleDetail.schedule', 'reviewedBy'])
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->whereHas('faculty', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('faculty_code', 'like', "%{$search}%");
            });
        }

        $total = $query->count();
        $items = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $formatted = $items->map(function (self $req) {
            $detail  = $req->scheduleDetail;
            $faculty = $req->faculty;

            return [
                'id'                    => $req->id,
                'faculty_name'          => $faculty
                    ? trim($faculty->first_name . ' ' . ($faculty->middle_name ? $faculty->middle_name[0] . '. ' : '') . $faculty->last_name)
                    : 'N/A',
                'faculty_code'          => $faculty?->faculty_code ?? 'N/A',
                'department'            => $faculty?->department?->name ?? 'N/A',
                'schedule_detail_id'    => $req->schedule_detail_id,
                'original_day'          => $detail?->day_of_week ?? 'N/A',
                'original_time_in'      => $detail ? Carbon::parse($detail->time_in)->format('H:i') : '--:--',
                'original_time_out'     => $detail ? Carbon::parse($detail->time_out)->format('H:i') : '--:--',
                'original_room'         => $detail?->room ?? 'N/A',
                'original_subject'      => $detail?->subject_code ?? 'N/A',
                'original_subject_desc' => $detail?->subject_desc ?? null,
                'requested_day'         => $req->requested_day_of_week,
                'requested_time_in'     => Carbon::parse($req->requested_time_in)->format('H:i'),
                'requested_time_out'    => Carbon::parse($req->requested_time_out)->format('H:i'),
                'requested_room'        => $req->requested_room,
                'effective_date'        => $req->effective_date?->format('M d, Y'),
                'reason'                => $req->reason,
                'status'                => $req->status,
                'reviewed_by_email'     => $req->reviewedBy?->email ?? null,
                'reviewed_at'           => $req->reviewed_at?->format('M d, Y h:i A'),
                'review_remarks'        => $req->review_remarks,
                'created_at'            => $req->created_at?->format('M d, Y h:i A'),
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

    /* ------------------------------------------------------------------ */
    /*  Faculty Query: paginated own requests                             */
    /* ------------------------------------------------------------------ */

    /**
     * Get paginated change requests for a specific faculty member.
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
                'id'                   => $req->id,
                'schedule_detail_id'   => $req->schedule_detail_id,
                'original_day'         => $detail?->day_of_week ?? 'N/A',
                'original_time_in'     => $detail ? Carbon::parse($detail->time_in)->format('H:i') : '--:--',
                'original_time_out'    => $detail ? Carbon::parse($detail->time_out)->format('H:i') : '--:--',
                'original_room'        => $detail?->room ?? 'N/A',
                'original_subject'     => $detail?->subject_code ?? 'N/A',
                'requested_day'        => $req->requested_day_of_week,
                'requested_time_in'    => Carbon::parse($req->requested_time_in)->format('H:i'),
                'requested_time_out'   => Carbon::parse($req->requested_time_out)->format('H:i'),
                'requested_room'       => $req->requested_room,
                'effective_date'       => $req->effective_date?->format('M d, Y'),
                'reason'               => $req->reason,
                'status'               => $req->status,
                'reviewed_by'          => $req->reviewedBy?->email ?? null,
                'reviewed_at'          => $req->reviewed_at?->format('M d, Y h:i A'),
                'review_remarks'       => $req->review_remarks,
                'created_at'           => $req->created_at?->format('M d, Y h:i A'),
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
