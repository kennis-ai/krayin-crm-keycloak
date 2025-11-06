<?php

namespace Webkul\KeycloakSSO\Exceptions;

use Exception;

/**
 * Base exception for all Keycloak-related errors
 */
class KeycloakException extends Exception
{
    /**
     * Create a new Keycloak exception instance.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = 'Keycloak operation failed', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the exception as an array for logging.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
        ];
    }
}
