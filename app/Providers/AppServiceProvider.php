<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\Document;
use App\Observers\ApplicationObserver;
use App\Observers\DocumentObserver;
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

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Application::observe(ApplicationObserver::class);
        Document::observe(DocumentObserver::class);
    }
}
