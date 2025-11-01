<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\Conversation;
use App\Models\Document;
use App\Observers\ApplicationObserver;
use App\Observers\ConversationObserver;
use App\Observers\DocumentObserver;
use Illuminate\Support\Facades\Blade;
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
        Conversation::observe(ConversationObserver::class);
        Document::observe(DocumentObserver::class);

        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->is_admin;
        });
    }
}
