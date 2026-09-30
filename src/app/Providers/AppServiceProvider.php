<?php

namespace App\Providers;

use App\Contracts\GeocodingProvider;
use App\Providers\Geocoding\NominatimProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(GeocodingProvider::class, function ($app) {
            return new NominatimProvider();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
