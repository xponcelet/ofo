<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Exemple : 'App\Models\Post' => 'App\Policies\PostPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Autorisation explicite (utile si on veut l’appeler manuellement)
        Gate::define('access-admin', fn($user) => $user->isAdmin());

        // Donne tous les droits aux admins par défaut
        Gate::before(function ($user, $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });
    }
}
