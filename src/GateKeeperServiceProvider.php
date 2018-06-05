<?php

namespace Jenky\GateKeeper;

use Illuminate\Support\ServiceProvider;

class GateKeeperServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \App\User::observe(GateKeeperObserver::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
