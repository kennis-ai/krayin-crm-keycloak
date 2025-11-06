<?php

namespace Webkul\KeycloakSSO\Providers;

use Konekt\Concord\BaseModuleServiceProvider;

/**
 * ModuleServiceProvider
 *
 * This service provider integrates the Keycloak SSO package with Krayin CRM
 * using the Concord module system.
 */
class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * The namespace for the package.
     *
     * @var string
     */
    protected $namespace = 'Webkul\KeycloakSSO';

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/keycloak-routes.php');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'keycloak-sso');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'keycloak-sso');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->registerConfig();
    }

    /**
     * Register package configuration.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/keycloak.php',
            'keycloak'
        );
    }
}
