<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // The personal_access_tokens table already exists in the connected
        // database (created via the consolidated create_all_tables migration),
        // so skip Sanctum's own migration to avoid "already exists" errors.
        // Must run in register() — package providers boot before this one,
        // so calling it in boot() would be too late.
        \Laravel\Sanctum\Sanctum::ignoreMigrations();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
