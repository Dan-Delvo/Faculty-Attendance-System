<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\OnlineAttendanceRequest;
use App\Services\OnlineAttendanceSyncService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('online-attendance:sync-approved', function () {
    $syncService = app(OnlineAttendanceSyncService::class);

    $updated = 0;
    $errors = 0;

    OnlineAttendanceRequest::query()
        ->where('status', 'approved')
        ->orderBy('id')
        ->chunkById(100, function ($requests) use ($syncService, &$updated, &$errors) {
            foreach ($requests as $request) {
                try {
                    DB::transaction(function () use ($syncService, $request) {
                        $fresh = OnlineAttendanceRequest::whereKey($request->id)
                            ->lockForUpdate()
                            ->first();

                        if ($fresh && $fresh->status === 'approved') {
                            $syncService->syncApprovedRequest($fresh);
                        }
                    });
                    $updated++;
                } catch (\RuntimeException $e) {
                    $errors++;
                    $this->warn("Skipped request #{$request->id}: {$e->getMessage()}");
                }
            }
        });

    $this->info("Synced approved requests: {$updated}");
    $this->info("Skipped with issues: {$errors}");
})->purpose('Sync approved online attendance requests into attendance_records');
