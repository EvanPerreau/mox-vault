<?php

namespace Modules\UpdateSetsCommand;

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\ServiceProvider;
use Modules\UpdateSetsCommand\commands\UpdateSetsCommand;

/**
 * Service provider for the UpdateSetsCommand module.
 *
 * @package Modules\UpdateSetsCommand
 */
class UpdateSetsCommandServiceProvider extends ServiceProvider
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
        if ($this->app->runningInConsole()) {
            $this->commands($this->getCommands());
        }

        Schedule::command('update:sets')->dailyAt('00:00');
    }

    /**
     * Get all commands in the commands directory.
     *
     * @return array<int, string>
     */
    private function getCommands(): array
    {
        $commandsPath = __DIR__ . '/commands';
        $namespace = 'Modules\\UpdateSetsCommand\\commands\\';

        if (!is_dir($commandsPath)) {
            return [];
        }

        $commands = [];
        $files = scandir($commandsPath);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (is_file($commandsPath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $className = pathinfo($file, PATHINFO_FILENAME);
                $commands[] = $namespace . $className;
            }
        }

        return $commands;
    }
}
