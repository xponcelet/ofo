<?php

namespace App\Providers;

use App\Models\Trip;
use App\Policies\TripPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //  Liaison explicite entre modèle et policy
        Gate::policy(Trip::class, TripPolicy::class);
    }
}
