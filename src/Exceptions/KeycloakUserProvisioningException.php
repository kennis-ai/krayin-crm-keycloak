<?php

namespace Webkul\KeycloakSSO\Exceptions;

/**
 * Exception thrown when user provisioning fails
 */
class KeycloakUserProvisioningException extends KeycloakException
{
    /**
     * Create a new user provisioning exception instance.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = 'User provisioning failed', int $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception for user creation failure.
     *
     * @param  string  $email
     * @param  string|null  $reason
     * @return static
     */
    public static function creationFailed(string $email, ?string $reason = null): static
    {
        $message = "Failed to create user with email: {$email}";

        if ($reason) {
            $message .= " - Reason: {$reason}";
        }

        return new static($message, 500);
    }

    /**
     * Create exception for user update failure.
     *
     * @param  string  $email
     * @param  string|null  $reason
     * @return static
     */
    public static function updateFailed(string $email, ?string $reason = null): static
    {
        $message = "Failed to update user with email: {$email}";

        if ($reason) {
            $message .= " - Reason: {$reason}";
        }

        return new static($message, 500);
    }

    /**
     * Create exception for missing required user data.
     *
     * @param  string  $field
     * @return static
     */
    public static function missingRequiredField(string $field): static
    {
        return new static("Required user field '{$field}' is missing from Keycloak user data", 422);
    }

    /**
     * Create exception for role mapping failure.
     *
     * @param  string  $email
     * @param  string|null  $reason
     * @return static
     */
    public static function roleMappingFailed(string $email, ?string $reason = null): static
    {
        $message = "Failed to map roles for user: {$email}";

        if ($reason) {
            $message .= " - Reason: {$reason}";
        }

        return new static($message, 500);
    }

    /**
     * Create exception for duplicate user.
     *
     * @param  string  $email
     * @return static
     */
    public static function duplicateUser(string $email): static
    {
        return new static("User with email '{$email}' already exists with different authentication provider", 409);
    }

    /**
     * Create exception for invalid user data.
     *
     * @param  string  $reason
     * @return static
     */
    public static function invalidUserData(string $reason): static
    {
        return new static("Invalid user data: {$reason}", 422);
    }
}
