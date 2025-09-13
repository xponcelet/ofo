<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Trip;
use App\Policies\TripPolicy;

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
        //  Enregistre la policy Trip → indispensable pour que Gate/authorize() fonctionne
        Gate::policy(Trip::class, TripPolicy::class);
    }
}
