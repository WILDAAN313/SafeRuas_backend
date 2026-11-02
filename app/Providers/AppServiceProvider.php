<?php

namespace App\Providers;

use App\Support\IndramayuBoundary;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(IndramayuBoundary::class, static fn (): IndramayuBoundary => IndramayuBoundary::fromGeoJsonFile(resource_path('indramayu.geojson')));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
