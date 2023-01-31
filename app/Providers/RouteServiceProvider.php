<?php

namespace App\Providers;

use App\Models\Buildings\Building;
use App\Models\Meters\Meter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->defineRouteBindings();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Define explicit logic for how parameters in routes rules will be cast to models.
     *
     * @return void
     */
    protected function defineRouteBindings()
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
