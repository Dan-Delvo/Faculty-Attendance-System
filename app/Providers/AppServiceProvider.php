<?php

namespace App\Providers;

use App\Models\OnlineAttendanceRequest;
use App\Observers\OnlineAttendanceRequestObserver;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        OnlineAttendanceRequest::observe(OnlineAttendanceRequestObserver::class);
    }
}
