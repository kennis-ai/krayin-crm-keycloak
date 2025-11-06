<?php

namespace Webkul\KeycloakSSO\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * KeycloakLoginSuccessful Event
 *
 * Fired when a user successfully authenticates via Keycloak.
 *
 * @todo Implementation in Phase 9: Event System
 */
class KeycloakLoginSuccessful
{
    use Dispatchable, SerializesModels;

    /**
     * The authenticated user.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    public $user;

    /**
     * Keycloak user data.
     *
     * @var array
     */
    public $keycloakData;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $keycloakData
     * @return void
     */
    public function __construct($user, array $keycloakData)
    {
        $this->user = $user;
        $this->keycloakData = $keycloakData;
    }
}
