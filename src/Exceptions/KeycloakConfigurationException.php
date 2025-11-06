<?php

namespace Webkul\KeycloakSSO\Exceptions;

/**
 * Exception thrown when Keycloak configuration is invalid or missing
 */
class KeycloakConfigurationException extends KeycloakException
{
    /**
     * Create a new configuration exception instance.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = 'Keycloak configuration is invalid', int $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception for missing configuration.
     *
     * @param  string  $key
     * @return static
     */
    public static function missing(string $key): static
    {
        return new static("Required Keycloak configuration key '{$key}' is missing");
    }

    /**
     * Create exception for invalid configuration.
     *
     * @param  string  $key
     * @param  string  $reason
     * @return static
     */
    public static function invalid(string $key, string $reason = ''): static
    {
        $message = "Keycloak configuration key '{$key}' is invalid";

        if ($reason) {
            $message .= ": {$reason}";
        }

        return new static($message);
    }
}
