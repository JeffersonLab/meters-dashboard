<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        foreach (glob(base_path().'/resources/macros/*.php') as $macroFile) {
            require_once $macroFile;
        }
    }
}
