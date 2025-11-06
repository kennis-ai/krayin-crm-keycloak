<?php

namespace Webkul\KeycloakSSO\Exceptions;

/**
 * Exception thrown when Keycloak authentication fails
 */
class KeycloakAuthenticationException extends KeycloakException
{
    /**
     * Create a new authentication exception instance.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = 'Keycloak authentication failed', int $code = 401, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
