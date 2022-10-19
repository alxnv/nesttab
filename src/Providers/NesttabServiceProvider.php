<?php

namespace Alxnv\Nesttab\Providers;

use Illuminate\Support\ServiceProvider;

class NesttabServiceProvider extends ServiceProvider
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
        //
		$this->loadRoutesFrom(__DIR__.'../../routes/web.php');

    }
}
