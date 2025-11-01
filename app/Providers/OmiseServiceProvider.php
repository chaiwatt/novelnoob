<?php

namespace App\Providers;

use App\Omise\process\OmiseCharge;
use App\Omise\process\OmiseSource;
use Illuminate\Support\ServiceProvider;

class OmiseServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->call([$this, 'registerOmiseCharge']);
        $this->app->call([$this, 'registerOmiseSource']);
    }

    /**
     * Register OmiseCharge as a singleton.
     */
    public function registerOmiseCharge()
    {
        $this->app->singleton(OmiseCharge::class, function () {
            return new OmiseCharge;
        });
    }

    /**
     * Register OmiseSource as a singleton.
     */
    public function registerOmiseSource()
    {
        $this->app->singleton(OmiseSource::class, function () {
            return new OmiseSource;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
