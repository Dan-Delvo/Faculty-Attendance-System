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
