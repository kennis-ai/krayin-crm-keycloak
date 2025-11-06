<?php

namespace Webkul\KeycloakSSO\Listeners;

use Webkul\KeycloakSSO\Events\KeycloakLoginSuccessful;

/**
 * SyncKeycloakUser Listener
 *
 * Synchronizes user data from Keycloak after successful login.
 *
 * @todo Implementation in Phase 9: Event System
 */
class SyncKeycloakUser
{
    /**
     * Handle the event.
     *
     * @param  \Webkul\KeycloakSSO\Events\KeycloakLoginSuccessful  $event
     * @return void
     */
    public function handle(KeycloakLoginSuccessful $event)
    {
        // Implementation in Phase 9
    }
}
