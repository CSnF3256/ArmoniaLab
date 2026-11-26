<?php

namespace App\Providers;

use App\Contracts\AcordeServiceContract;
use App\Contracts\RecomendacionServiceContract;
use App\Services\AcordeService;
use App\Services\RecomendacionService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AcordeServiceContract::class, AcordeService::class);
        $this->app->bind(RecomendacionServiceContract::class, RecomendacionService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
