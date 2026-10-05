<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('ALL', function ($user) {
            return in_array($user->type, [User::TYPE_ADMIN, User::TYPE_LPI, User::TYPE_REVIEWER, User::TYPE_LPI_REVIEWER, User::TYPE_TEST]);
        });

        Gate::define('ADMIN', function ($user) {
            return in_array($user->type, [User::TYPE_ADMIN]);
        });

        Gate::define('LPI', function ($user) {
            return in_array($user->type, [User::TYPE_LPI]);
        });

        Gate::define('REVIEWER', function ($user) {
            return in_array($user->type, [User::TYPE_REVIEWER]);
        });

        Gate::define('LPIREVIEWER', function ($user) {
            return in_array($user->type, [User::TYPE_LPI_REVIEWER]);
        });

        Gate::define('ADMIN_LPI', function ($user) {
            return in_array($user->type, [User::TYPE_ADMIN, User::TYPE_LPI]);
        });

        Gate::define('ADMIN_REVIEWER', function ($user) {
            return in_array($user->type, [User::TYPE_ADMIN, User::TYPE_REVIEWER]);
        });

        Gate::define('ADMIN_LPIREVIEWER', function ($user) {
            return in_array($user->type, [User::TYPE_ADMIN, User::TYPE_LPI_REVIEWER]);
        });

        Gate::define('REVIEWER_LPIREVIEWER', function ($user) {
            return in_array($user->type, [User::TYPE_REVIEWER, User::TYPE_LPI_REVIEWER]);
        });

        Gate::define('LPI_LPIREVIEWER', function ($user) {
            return in_array($user->type, [User::TYPE_LPI, User::TYPE_LPI_REVIEWER]);
        });

        Gate::define('LPI_REVIEWER', function ($user) {
            return in_array($user->type, [User::TYPE_LPI, User::TYPE_REVIEWER]);
        });

        Gate::define('LPI_REVIEWER_LPIREVIEWER', function ($user) {
            return in_array($user->type, [User::TYPE_LPI, User::TYPE_REVIEWER, User::TYPE_LPI_REVIEWER]);
        });

        Gate::define('TEST', function ($user) {
            return in_array($user->type, [User::TYPE_TEST]);
        });
    }
}
