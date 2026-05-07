<?php

namespace App\Providers;

use App\Services\C45AlgorithmService;
use App\Services\TrainingService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(C45AlgorithmService::class, function ($app) {
            return new C45AlgorithmService();
        });

        $this->app->singleton(TrainingService::class, function ($app) {
            return new TrainingService($app->make(C45AlgorithmService::class));
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
