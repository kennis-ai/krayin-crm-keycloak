<?php

namespace Webkul\KeycloakSSO\Listeners;

use Webkul\KeycloakSSO\Events\KeycloakLogoutSuccessful;

/**
 * HandleKeycloakLogout Listener
 *
 * Handles cleanup after successful Keycloak logout.
 *
 * @todo Implementation in Phase 9: Event System
 */
class HandleKeycloakLogout
{
    /**
     * Handle the event.
     *
     * @param  \Webkul\KeycloakSSO\Events\KeycloakLogoutSuccessful  $event
     * @return void
     */
    public function handle(KeycloakLogoutSuccessful $event)
    {
        // Implementation in Phase 9
    }
}
