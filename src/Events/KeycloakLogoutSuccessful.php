<?php

namespace Webkul\KeycloakSSO\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * KeycloakLogoutSuccessful Event
 *
 * Fired when a user successfully logs out via Keycloak.
 *
 * @todo Implementation in Phase 9: Event System
 */
class KeycloakLogoutSuccessful
{
    use Dispatchable, SerializesModels;

    /**
     * The logged out user.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}
