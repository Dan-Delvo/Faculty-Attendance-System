<?php

namespace App\Observers;

use App\Models\OnlineAttendanceRequest;
use App\Services\OnlineAttendanceSyncService;

class OnlineAttendanceRequestObserver
{
    public function created(OnlineAttendanceRequest $request): void
    {
        if ($request->status === 'approved') {
            app(OnlineAttendanceSyncService::class)->syncApprovedRequest($request);
        }
    }

    public function updated(OnlineAttendanceRequest $request): void
    {
        // Sync when transitioning to approved, or when approved times are edited.
        $becameApproved = $request->wasChanged('status') && $request->status === 'approved';
        $approvedTimesUpdated = $request->status === 'approved' && ($request->wasChanged('time_in') || $request->wasChanged('time_out'));

        if ($becameApproved || $approvedTimesUpdated) {
            app(OnlineAttendanceSyncService::class)->syncApprovedRequest($request);
        }
    }
}
