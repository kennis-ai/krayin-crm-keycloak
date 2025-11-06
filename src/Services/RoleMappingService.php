<?php

namespace Webkul\KeycloakSSO\Services;

/**
 * RoleMappingService
 *
 * Maps Keycloak roles to Krayin CRM roles.
 *
 * @todo Implementation in Phase 6: User Provisioning
 */
class RoleMappingService
{
    /**
     * Role mapping configuration.
     *
     * @var array
     */
    protected $roleMapping;

    /**
     * Create a new RoleMappingService instance.
     *
     * @param  array  $roleMapping
     * @return void
     */
    public function __construct(array $roleMapping)
    {
        $this->roleMapping = $roleMapping;
    }

    // Methods will be implemented in Phase 6
}
