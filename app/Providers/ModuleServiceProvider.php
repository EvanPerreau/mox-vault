<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

/**
 * Service provider that loads all module service providers.
 */
class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Get all directories in the modules folder
        $modulesPath = base_path('modules');
        
        if (!File::exists($modulesPath)) {
            return;
        }
        
        $modules = File::directories($modulesPath);
        
        foreach ($modules as $modulePath) {
            $moduleName = basename($modulePath);
            $providerClass = "Modules\\{$moduleName}\\{$moduleName}ServiceProvider";
            
            // Check if the provider file exists
            $providerFile = "{$modulePath}/{$moduleName}ServiceProvider.php";
            
            if (File::exists($providerFile) && class_exists($providerClass)) {
                $this->app->register($providerClass);
            }
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
