<?php

namespace Alxnv\Nesttab\Providers;

//use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

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
        //dd(__DIR__.'/../../../routes/web.php');
		/*
        $this->routes(function () {
            /*Route::middleware('api')
                ->prefix('api')
                ->namespace('Alxnv\Nesttab\Http\Controllers')
                ->group(base_path('routes/api.php'));
            Route::middleware('web')
                ->namespace('Alxnv\Nesttab\Http\Controllers') // this line does not work
                ->group(__DIR__.'/../../routes/web.php');
        });*/
        Route::middlewareGroup('nesttab', ['web']);
    /*
       вместо ['web'] наверху можно
	'middleware' => [
        'web',
        Authorize::class,
    ],*/

        $this->registerRoutes();

        $viewsDirectory = __DIR__.'/../../resources/views';            
        //dd($viewsDirectory);
        //and then set the package viewDirectory
        $this->loadViewsFrom($viewsDirectory, 'nesttab');
        $this->loadJsonTranslationsFrom(__DIR__.'/../../resources/lang');

        Route::pattern('id', '[0-9]+');
    }
	/**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
			$this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        });
    }

    /**
     * Get the Telescope route group configuration array.
     *
     * @return array
     */
    private function routeConfiguration()
    {
        return [
            'domain' => null, //config('telescope.domain', null),
            'namespace' => 'Alxnv\Nesttab\Http\Controllers',
            //'prefix' => config('telescope.path'),
            'middleware' => 'nesttab',
        ];
    }
}
