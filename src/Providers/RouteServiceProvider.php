<?php

namespace Alxnv\Nesttab\Providers;

//use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        
        $this->routes(function () {
        /*    Route::middleware('api')
                ->prefix('api')
                ->namespace('Alxnv\Nesttab\src\Http\Controllers')
                ->group(base_path('routes/api.php'));
    */
            Route::middleware('web')
                ->namespace('Alxnv\Nesttab\Http\Controllers') // this line does not work
                ->group(vendor_path('Alxnv\Nesttab\routes\web.php'));
        });

        $viewsDirectory = __DIR__.'/../../resources/views';            
        //dd($viewsDirectory);
        //and then set the package viewDirectory
        $this->loadViewsFrom($viewsDirectory, 'nesttab');
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

    }
}
