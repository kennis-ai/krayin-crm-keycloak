<?php

namespace Webkul\KeycloakSSO\Exceptions;

/**
 * Exception thrown when a Keycloak token has expired
 */
class KeycloakTokenExpiredException extends KeycloakTokenException
{
    /**
     * Create a new token expired exception instance.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = 'Keycloak token has expired', int $code = 401, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception for expired access token.
     *
     * @return static
     */
    public static function accessToken(): static
    {
        return new static('Access token has expired', 401);
    }

    /**
     * Create exception for expired refresh token.
     *
     * @return static
     */
    public static function refreshToken(): static
    {
        return new static('Refresh token has expired', 401);
    }

    /**
     * Create exception with custom expiry details.
     *
     * @param  string  $tokenType
     * @param  string|null  $expiresAt
     * @return static
     */
    public static function withDetails(string $tokenType, ?string $expiresAt = null): static
    {
        $message = "{$tokenType} token has expired";

        if ($expiresAt) {
            $message .= " (expired at: {$expiresAt})";
        }

        return new static($message, 401);
    }
}
