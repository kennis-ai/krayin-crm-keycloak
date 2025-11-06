<?php

namespace Webkul\KeycloakSSO\Services;

/**
 * UserProvisioningService
 *
 * Handles automatic user provisioning and synchronization from Keycloak.
 *
 * @todo Implementation in Phase 6: User Provisioning
 */
class UserProvisioningService
{
    /**
     * Role mapping service instance.
     *
     * @var \Webkul\KeycloakSSO\Services\RoleMappingService
     */
    protected $roleMappingService;

    /**
     * Create a new UserProvisioningService instance.
     *
     * @param  \Webkul\KeycloakSSO\Services\RoleMappingService  $roleMappingService
     * @return void
     */
    public function __construct(RoleMappingService $roleMappingService)
    {
        $this->roleMappingService = $roleMappingService;
    }

    // Methods will be implemented in Phase 6
}
