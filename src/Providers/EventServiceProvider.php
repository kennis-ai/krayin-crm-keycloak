<?php

namespace Webkul\KeycloakSSO\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * EventServiceProvider
 *
 * Registers event listeners for Keycloak authentication lifecycle events.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the package.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        /**
         * Keycloak Login Successful Event
         *
         * Fired when a user successfully authenticates via Keycloak.
         * Listeners can sync user data, log activity, or perform post-login actions.
         */
        \Webkul\KeycloakSSO\Events\KeycloakLoginSuccessful::class => [
            \Webkul\KeycloakSSO\Listeners\SyncKeycloakUser::class,
        ],

        /**
         * Keycloak Login Failed Event
         *
         * Fired when Keycloak authentication fails.
         * Listeners can log errors, send notifications, or trigger fallback mechanisms.
         */
        \Webkul\KeycloakSSO\Events\KeycloakLoginFailed::class => [
            // Listeners will be added in Phase 9
        ],

        /**
         * Keycloak Logout Successful Event
         *
         * Fired when a user successfully logs out via Keycloak.
         * Listeners can clean up sessions, revoke tokens, or log activity.
         */
        \Webkul\KeycloakSSO\Events\KeycloakLogoutSuccessful::class => [
            \Webkul\KeycloakSSO\Listeners\HandleKeycloakLogout::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array<int, class-string>
     */
    protected $subscribe = [
        // Event subscribers can be added here for grouped event handling
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
