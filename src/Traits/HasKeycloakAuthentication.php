<?php

namespace Webkul\KeycloakSSO\Traits;

use Illuminate\Support\Facades\Crypt;

/**
 * HasKeycloakAuthentication Trait
 *
 * Add this trait to the User model to enable Keycloak SSO functionality.
 *
 * Usage:
 * ```php
 * use Webkul\KeycloakSSO\Traits\HasKeycloakAuthentication;
 *
 * class User extends Model
 * {
 *     use HasKeycloakAuthentication;
 * }
 * ```
 */
trait HasKeycloakAuthentication
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected function keycloakCasts(): array
    {
        return [
            'keycloak_token_expires_at' => 'datetime',
        ];
    }

    /**
     * Boot the trait.
     *
     * @return void
     */
    public static function bootHasKeycloakAuthentication()
    {
        // Automatically merge casts
        static::retrieved(function ($model) {
            $model->mergeCasts($model->keycloakCasts());
        });
    }

    /**
     * Check if user is authenticated via Keycloak.
     *
     * @return bool
     */
    public function isKeycloakUser(): bool
    {
        return $this->auth_provider === 'keycloak';
    }

    /**
     * Check if user is authenticated locally.
     *
     * @return bool
     */
    public function isLocalUser(): bool
    {
        return $this->auth_provider === 'local';
    }

    /**
     * Get the Keycloak user ID.
     *
     * @return string|null
     */
    public function getKeycloakId(): ?string
    {
        return $this->keycloak_id;
    }

    /**
     * Set the Keycloak user ID.
     *
     * @param  string  $keycloakId
     * @return self
     */
    public function setKeycloakId(string $keycloakId): self
    {
        $this->keycloak_id = $keycloakId;

        return $this;
    }

    /**
     * Get the decrypted Keycloak refresh token.
     *
     * @return string|null
     */
    public function getKeycloakRefreshToken(): ?string
    {
        if (empty($this->keycloak_refresh_token)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->keycloak_refresh_token);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Set the Keycloak refresh token (encrypted).
     *
     * @param  string|null  $refreshToken
     * @return self
     */
    public function setKeycloakRefreshToken(?string $refreshToken): self
    {
        if ($refreshToken === null) {
            $this->keycloak_refresh_token = null;
        } else {
            $this->keycloak_refresh_token = Crypt::encryptString($refreshToken);
        }

        return $this;
    }

    /**
     * Check if the Keycloak token has expired.
     *
     * @return bool
     */
    public function hasKeycloakTokenExpired(): bool
    {
        if (! $this->isKeycloakUser() || ! $this->keycloak_token_expires_at) {
            return false;
        }

        return now()->isAfter($this->keycloak_token_expires_at);
    }

    /**
     * Check if the Keycloak token is expiring soon.
     *
     * @param  int  $gracePeriodSeconds  Grace period in seconds (default: 300 = 5 minutes)
     * @return bool
     */
    public function isKeycloakTokenExpiringSoon(int $gracePeriodSeconds = 300): bool
    {
        if (! $this->isKeycloakUser() || ! $this->keycloak_token_expires_at) {
            return false;
        }

        $expiresAt = $this->keycloak_token_expires_at;
        $gracePeriodEnd = $expiresAt->subSeconds($gracePeriodSeconds);

        return now()->isAfter($gracePeriodEnd);
    }

    /**
     * Update Keycloak token expiration.
     *
     * @param  int  $expiresIn  Seconds until expiration
     * @return self
     */
    public function updateKeycloakTokenExpiration(int $expiresIn): self
    {
        $this->keycloak_token_expires_at = now()->addSeconds($expiresIn);

        return $this;
    }

    /**
     * Clear Keycloak authentication data.
     *
     * @return self
     */
    public function clearKeycloakData(): self
    {
        $this->keycloak_refresh_token = null;
        $this->keycloak_token_expires_at = null;

        return $this;
    }

    /**
     * Scope query to only Keycloak users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeKeycloakUsers($query)
    {
        return $query->where('auth_provider', 'keycloak');
    }

    /**
     * Scope query to only local users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLocalUsers($query)
    {
        return $query->where('auth_provider', 'local');
    }

    /**
     * Scope query to users with expired Keycloak tokens.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithExpiredKeycloakTokens($query)
    {
        return $query->where('auth_provider', 'keycloak')
            ->whereNotNull('keycloak_token_expires_at')
            ->where('keycloak_token_expires_at', '<', now());
    }
}
