<?php

namespace App\Providers;

use App\Models\Server;
use App\Models\Site;
use App\Policies\ServerPolicy;
use App\Policies\SitePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Server::class => ServerPolicy::class,
        Site::class => SitePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function ($user, string $ability) {
            // Emergency bypass for the configured panel admin account.
            if (config('security.rbac.panel_admin_bypass', true)) {
                $panelAdminIdentifier = config('security.rbac.panel_admin_identifier', config('cipi.username'));

                if (is_string($panelAdminIdentifier)
                    && $panelAdminIdentifier !== ''
                    && is_object($user)
                    && isset($user->email)
                    && $user->email === $panelAdminIdentifier) {
                    return true;
                }
            }

            // Standard super-admin bypass when roles are seeded.
            if (is_object($user)
                && method_exists($user, 'hasRole')
                && $user->hasRole('Super Admin')) {
                return true;
            }

            return null;
        });
    }
}
