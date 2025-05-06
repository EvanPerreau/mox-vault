<?php

namespace Modules\SetsDatabase;

use Illuminate\Support\ServiceProvider;

/**
 * Service provider for the Card module.
 *
 * @package Modules\CardDatabase
 */
class SetsDatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register any module services.
     *
     * @return void
     */
    public function register(): void
    {
        // Register module bindings
    }

    /**
     * Bootstrap any module services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }
}
