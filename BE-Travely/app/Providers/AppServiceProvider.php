<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Models\Tour;
use App\Observers\TourObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Force HTTPS URLs for assets and routes
        if (config('app.env') !== 'local' || request()->secure()) {
            URL::forceScheme('https');
        }

        // Register Tour Observer for automatic cache clearing
        Tour::observe(TourObserver::class);
    }
}
