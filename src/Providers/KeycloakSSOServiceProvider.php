<?php

namespace Webkul\KeycloakSSO\Providers;

use Illuminate\Support\ServiceProvider;
use Webkul\KeycloakSSO\Services\KeycloakService;
use Webkul\KeycloakSSO\Services\UserProvisioningService;
use Webkul\KeycloakSSO\Services\RoleMappingService;

/**
 * KeycloakSSOServiceProvider
 *
 * Main service provider for the Keycloak SSO package.
 * Handles service registration, configuration publishing, and package setup.
 */
class KeycloakSSOServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/keycloak.php',
            'keycloak'
        );

        // Register core services as singletons
        $this->registerServices();

        // Register event service provider
        $this->app->register(EventServiceProvider::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Only load package resources if Keycloak SSO is enabled
        if (! $this->app->runningInConsole() && ! config('keycloak.enabled')) {
            return;
        }

        // Publish configuration
        $this->publishes([
            __DIR__ . '/../Config/keycloak.php' => config_path('keycloak.php'),
        ], 'keycloak-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../Database/Migrations' => database_path('migrations'),
        ], 'keycloak-migrations');

        // Publish views
        $this->publishes([
            __DIR__ . '/../Resources/views' => resource_path('views/vendor/keycloak-sso'),
        ], 'keycloak-views');

        // Publish translations
        $this->publishes([
            __DIR__ . '/../Resources/lang' => $this->app->langPath('vendor/keycloak-sso'),
        ], 'keycloak-lang');

        // Load package resources
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'keycloak-sso');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'keycloak-sso');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/keycloak-routes.php');

        // Register middleware
        $this->registerMiddleware();

        // Register commands if running in console
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    /**
     * Register core services.
     *
     * @return void
     */
    protected function registerServices()
    {
        // Register KeycloakService as singleton
        $this->app->singleton(KeycloakService::class, function ($app) {
            return new KeycloakService(
                $app['config']['keycloak']
            );
        });

        // Register RoleMappingService as singleton
        $this->app->singleton(RoleMappingService::class, function ($app) {
            return new RoleMappingService(
                $app['config']['keycloak.role_mapping'] ?? [],
                $app['config']['keycloak.default_role'] ?? 'Sales',
                $app['config']['keycloak.sync_roles'] ?? true
            );
        });

        // Register UserProvisioningService as singleton
        $this->app->singleton(UserProvisioningService::class, function ($app) {
            return new UserProvisioningService(
                $app->make(RoleMappingService::class),
                $app->make(KeycloakService::class),
                $app['config']['keycloak.auto_provision_users'] ?? true,
                $app['config']['keycloak.sync_user_data'] ?? true
            );
        });

        // Register aliases
        $this->app->alias(KeycloakService::class, 'keycloak');
        $this->app->alias(UserProvisioningService::class, 'keycloak.provisioning');
        $this->app->alias(RoleMappingService::class, 'keycloak.roles');
    }

    /**
     * Register middleware.
     *
     * @return void
     */
    protected function registerMiddleware()
    {
        $router = $this->app['router'];

        // Register middleware aliases
        $router->aliasMiddleware('keycloak.auth', \Webkul\KeycloakSSO\Http\Middleware\KeycloakAuthenticate::class);
        $router->aliasMiddleware('keycloak.refresh', \Webkul\KeycloakSSO\Http\Middleware\KeycloakTokenRefresh::class);
    }

    /**
     * Register Artisan commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        // Commands will be registered here in future phases
        // Example: $this->commands([]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            KeycloakService::class,
            UserProvisioningService::class,
            RoleMappingService::class,
            'keycloak',
            'keycloak.provisioning',
            'keycloak.roles',
        ];
    }
}
