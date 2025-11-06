<?php

namespace Webkul\KeycloakSSO\Exceptions;

/**
 * Exception thrown when token operations fail
 */
class KeycloakTokenException extends KeycloakException
{
    /**
     * Create a new token exception instance.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = 'Token operation failed', int $code = 401, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception for expired token.
     *
     * @return static
     */
    public static function expired(): static
    {
        return new static('Access token has expired', 401);
    }

    /**
     * Create exception for invalid token.
     *
     * @return static
     */
    public static function invalid(): static
    {
        return new static('Access token is invalid', 401);
    }

    /**
     * Create exception for refresh failure.
     *
     * @return static
     */
    public static function refreshFailed(): static
    {
        return new static('Failed to refresh access token', 401);
    }
}
