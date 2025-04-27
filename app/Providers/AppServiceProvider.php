<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Console\Commands\MakeModuleCommand;

/**
 * Application Service Provider
 * 
 * Registers application services and components.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register services here
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register commands if we're in console
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModuleCommand::class,
            ]);
        }
    }
}
