<?php

namespace Webkul\KeycloakSSO\Services;

/**
 * KeycloakService
 *
 * Core service for Keycloak OAuth2/OpenID Connect integration.
 * Handles authentication flows, token management, and user information retrieval.
 *
 * @todo Implementation in Phase 4: Keycloak Service Integration
 */
class KeycloakService
{
    /**
     * Keycloak configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Create a new KeycloakService instance.
     *
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    // Methods will be implemented in Phase 4
}
