<?php

namespace Webkul\KeycloakSSO\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * KeycloakLoginFailed Event
 *
 * Fired when Keycloak authentication fails.
 *
 * @todo Implementation in Phase 9: Event System
 */
class KeycloakLoginFailed
{
    use Dispatchable, SerializesModels;

    /**
     * The exception that caused the failure.
     *
     * @var \Exception
     */
    public $exception;

    /**
     * Keycloak data (if available).
     *
     * @var array|null
     */
    public $keycloakData;

    /**
     * Create a new event instance.
     *
     * @param  \Exception  $exception
     * @param  array|null  $keycloakData
     * @return void
     */
    public function __construct(\Exception $exception, ?array $keycloakData = null)
    {
        $this->exception = $exception;
        $this->keycloakData = $keycloakData;
    }
}
