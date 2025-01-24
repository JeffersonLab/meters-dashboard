<?php

namespace App\Providers;

use App\Models\Buildings\Building;
use App\Models\Meters\Meter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->defineRouteBindings();

        // @see https://livewire.laravel.com/docs/installation#customizing-the-asset-url
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post($this->basePath().'livewire/update', $handle);
        });
        Livewire::setScriptRoute(function ($handle) {
            return Route::get($this->basePath().'livewire/livewire.js', $handle);
        });

    }

    /**
     * Return the path prefix for generating different urls to livewire in production vs. development.
     *
     * Accounts for the fact that the application runs at /apps/meters in production
     * rather than / during development.
     *
     * @return string
     */
    protected function basePath(): string {
       $parts =  parse_url(config('app.url'));
       if (isset($parts['path'])){
            $path = $parts['path'];
       }else{
           $path = '/';
       }
       if (! str_ends_with($path, '/')) {
           $path .= '/';
       }
       return $path;
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Define explicit logic for how parameters in routes rules will be cast to models.
     */
    protected function defineRouteBindings(): void
    {
        /*
         * Special route model bindings allowing the user to provide a
         * numeric id or a string name.
         */
        Route::bind('meter', function ($id) {
            if (is_numeric($id)) {
                return Meter::where('id', $id)->firstOrFail();
            } else {
                return Meter::where('name', $id)->firstOrFail();
            }
        });

        Route::bind('building', function ($id) {
            if (is_numeric($id)) {
                return Building::where('id', $id)->first();
            } else {
                return Building::where('name', $id)->first();
            }
        });
    }
}
