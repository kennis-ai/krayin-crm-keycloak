<?php

namespace Webkul\KeycloakSSO\Exceptions;

/**
 * Exception thrown when connection to Keycloak server fails
 */
class KeycloakConnectionException extends KeycloakException
{
    /**
     * Create a new connection exception instance.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = 'Failed to connect to Keycloak server', int $code = 503, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception for timeout.
     *
     * @return static
     */
    public static function timeout(): static
    {
        return new static('Connection to Keycloak server timed out', 504);
    }

    /**
     * Create exception for unreachable server.
     *
     * @return static
     */
    public static function unreachable(): static
    {
        return new static('Keycloak server is unreachable', 503);
    }
}
