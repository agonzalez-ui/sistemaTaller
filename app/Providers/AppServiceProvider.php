<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
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
        Gate::define('manage-security', fn (User $user) => $user->isAdministrator());
        Gate::define('module-access', fn (User $user, string $module, string $action = 'view') => $user->hasModulePermission($module, $action));
    }
}
