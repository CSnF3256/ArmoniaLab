<?php

namespace App\Providers;

use App\Contracts\AcordeServiceContract;
use App\Contracts\RecomendacionServiceContract;
use App\Contracts\TipoAcordeCatalogContract;
use App\Services\AcordeService;
use App\Services\ExerciseEvaluatorResolver;
use App\Services\RecomendacionService;
use App\Services\Recommendations\RecommendationFallbackResolver;
use App\Services\TipoAcordeCatalogService;
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
        $this->app->bind(TipoAcordeCatalogContract::class, TipoAcordeCatalogService::class);
        $this->app->singleton(ExerciseEvaluatorResolver::class);
        $this->app->singleton(RecommendationFallbackResolver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
