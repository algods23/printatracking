<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('components.topbar', function ($view) {
            $activities = \App\Models\ActivityLog::with('user')
                ->latest()
                ->limit(5)
                ->get();
            $view->with('topbarActivities', $activities);
        });
    }
}
