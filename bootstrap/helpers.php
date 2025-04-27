<?php

if (!function_exists('load_module_providers')) {
    function load_module_providers()
    {
        $providers = [];

        foreach (glob(base_path('modules/*/src/*ServiceProvider.php')) as $providerFile) {
            $providerNamespace = str_replace(
                ['/', '.php', base_path() . '/', 'modules/'],
                ['\\', '', '', 'Modules\\'],
                $providerFile
            );
            $providers[] = $providerNamespace;
        }

        return $providers;
    }
}
