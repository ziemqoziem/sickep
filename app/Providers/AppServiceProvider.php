<?php

namespace App\Providers;

use App\Models\MasterPegawai;
use App\Observers\MasterPegawaiObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        MasterPegawai::observe(MasterPegawaiObserver::class);
    }
}
