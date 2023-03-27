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
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->defineRouteBindings();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting(): void
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
