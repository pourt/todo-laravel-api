<?php

namespace PERP\Routes;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class Routes extends ServiceProvider
{
    public const HOME = '/home';

    protected $namespace = 'PERP\\';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->routes(function () {

            Route::middleware(
                [
                    'api',
                    'cors',
                    'json.response'
                ]
            )->group(base_path('PERP/Routes/app.php'));
        });
    }
}
