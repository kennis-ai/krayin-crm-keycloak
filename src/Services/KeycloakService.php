<?php

namespace Webkul\KeycloakSSO\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Webkul\KeycloakSSO\Exceptions\KeycloakAuthenticationException;
use Webkul\KeycloakSSO\Exceptions\KeycloakConfigurationException;
use Webkul\KeycloakSSO\Exceptions\KeycloakTokenException;

/**
 * KeycloakService
 *
 * Core service for Keycloak OAuth2/OpenID Connect integration.
 * Handles authentication flows, token management, and user information retrieval.
 */
class KeycloakService
{
    /**
     * Keycloak configuration.
     *
     * @var array
     */
    protected array $config;

    /**
     * Keycloak HTTP client.
     *
     * @var KeycloakClient
     */
    protected KeycloakClient $client;

    /**
     * Create a new KeycloakService instance.
     *
     * @param  array  $config
     *
     * @throws KeycloakConfigurationException
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->validateConfiguration();
        $this->initializeClient();
    }

    /**
     * Validate required configuration.
     *
     * @return void
     *
     * @throws KeycloakConfigurationException
     */
    protected function validateConfiguration(): void
    {
        $required = ['base_url', 'realm', 'client_id', 'client_secret', 'redirect_uri'];

        foreach ($required as $key) {
            if (empty($this->config[$key])) {
                throw KeycloakConfigurationException::missing($key);
            }
        }

        // Validate base URL format
        if (! filter_var($this->config['base_url'], FILTER_VALIDATE_URL)) {
            throw KeycloakConfigurationException::invalid('base_url', 'Must be a valid URL');
        }

        // Validate redirect URI format
        if (! filter_var($this->config['redirect_uri'], FILTER_VALIDATE_URL)) {
            throw KeycloakConfigurationException::invalid('redirect_uri', 'Must be a valid URL');
        }
    }

    /**
     * Initialize the Keycloak client.
     *
     * @return void
     */
    protected function initializeClient(): void
    {
        $timeout = $this->config['http_timeout'] ?? 30;

        $this->client = new KeycloakClient(
            $this->config['base_url'],
            $this->config['realm'],
            $timeout
        );
    }

    /**
     * Get the authorization URL for redirecting user to Keycloak login.
     *
     * @param  string|null  $state
     * @param  array  $scopes
     * @return string
     */
    public function getAuthorizationUrl(?string $state = null, array $scopes = ['openid', 'profile', 'email']): string
    {
        $state = $state ?? Str::random(40);

        // Store state in session for CSRF protection
        session(['keycloak_state' => $state]);

        $params = http_build_query([
            'client_id' => $this->config['client_id'],
            'redirect_uri' => $this->config['redirect_uri'],
            'response_type' => 'code',
            'scope' => implode(' ', $scopes),
            'state' => $state,
        ]);

        return $this->client->getAuthorizationEndpoint().'?'.$params;
    }

    /**
     * Handle the OAuth callback from Keycloak.
     *
     * @param  Request  $request
     * @return array
     *
     * @throws KeycloakAuthenticationException
     */
    public function handleCallback(Request $request): array
    {
        // Verify state for CSRF protection
        $state = $request->input('state');
        $sessionState = session('keycloak_state');

        if (! $state || $state !== $sessionState) {
            throw new KeycloakAuthenticationException('Invalid state parameter - possible CSRF attack');
        }

        // Clear the state from session
        session()->forget('keycloak_state');

        // Check for error response
        if ($request->has('error')) {
            $error = $request->input('error');
            $errorDescription = $request->input('error_description', $error);
            throw new KeycloakAuthenticationException("Keycloak authentication failed: {$errorDescription}");
        }

        // Get authorization code
        $code = $request->input('code');
        if (! $code) {
            throw new KeycloakAuthenticationException('Authorization code not provided');
        }

        // Exchange code for tokens
        $tokens = $this->client->getTokens(
            $code,
            $this->config['client_id'],
            $this->config['client_secret'],
            $this->config['redirect_uri']
        );

        // Get user information
        $userInfo = $this->client->getUserInfo($tokens['access_token']);

        return [
            'tokens' => $tokens,
            'user' => $userInfo,
        ];
    }

    /**
     * Get user information from access token.
     *
     * @param  string  $accessToken
     * @return array
     *
     * @throws KeycloakAuthenticationException
     */
    public function getUserInfo(string $accessToken): array
    {
        $cacheKey = 'keycloak_user_info_'.md5($accessToken);
        $cacheTtl = $this->config['cache_user_info'] ?? 300;

        if ($this->config['cache_user_info'] ?? false) {
            return Cache::remember($cacheKey, $cacheTtl, function () use ($accessToken) {
                return $this->client->getUserInfo($accessToken);
            });
        }

        return $this->client->getUserInfo($accessToken);
    }

    /**
     * Refresh an access token using a refresh token.
     *
     * @param  string  $refreshToken
     * @return array
     *
     * @throws KeycloakTokenException
     */
    public function refreshToken(string $refreshToken): array
    {
        try {
            $tokens = $this->client->refreshToken(
                $refreshToken,
                $this->config['client_id'],
                $this->config['client_secret']
            );

            Log::info('Keycloak token refreshed successfully');

            return $tokens;
        } catch (\Exception $e) {
            Log::error('Failed to refresh Keycloak token', [
                'exception' => $e->getMessage(),
            ]);
            throw KeycloakTokenException::refreshFailed();
        }
    }

    /**
     * Validate an access token.
     *
     * @param  string  $accessToken
     * @return bool
     */
    public function validateToken(string $accessToken): bool
    {
        try {
            $result = $this->client->introspectToken(
                $accessToken,
                $this->config['client_id'],
                $this->config['client_secret']
            );

            return $result['active'] ?? false;
        } catch (\Exception $e) {
            Log::error('Token validation failed', [
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Logout from Keycloak (revoke refresh token).
     *
     * @param  string  $refreshToken
     * @return bool
     */
    public function logout(string $refreshToken): bool
    {
        try {
            return $this->client->logout(
                $refreshToken,
                $this->config['client_id'],
                $this->config['client_secret']
            );
        } catch (\Exception $e) {
            Log::warning('Keycloak logout failed', [
                'exception' => $e->getMessage(),
            ]);

            // Return false but don't throw exception
            // as local session can still be cleared
            return false;
        }
    }

    /**
     * Get user roles from access token or user info.
     *
     * @param  string  $accessToken
     * @return array
     */
    public function getUserRoles(string $accessToken): array
    {
        try {
            $userInfo = $this->getUserInfo($accessToken);

            // Keycloak can store roles in different places depending on configuration
            // Check realm_access, resource_access, and roles claim
            $roles = [];

            // Check realm roles
            if (isset($userInfo['realm_access']['roles'])) {
                $roles = array_merge($roles, $userInfo['realm_access']['roles']);
            }

            // Check resource/client roles
            if (isset($userInfo['resource_access'][$this->config['client_id']]['roles'])) {
                $roles = array_merge($roles, $userInfo['resource_access'][$this->config['client_id']]['roles']);
            }

            // Check direct roles claim
            if (isset($userInfo['roles']) && is_array($userInfo['roles'])) {
                $roles = array_merge($roles, $userInfo['roles']);
            }

            return array_unique($roles);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user roles', [
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get the Keycloak client instance.
     *
     * @return KeycloakClient
     */
    public function getClient(): KeycloakClient
    {
        return $this->client;
    }

    /**
     * Get configuration value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Check if Keycloak SSO is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false);
    }

    /**
     * Get the logout redirect URL for Single Logout (SLO).
     *
     * @param  string|null  $postLogoutRedirectUri
     * @return string
     */
    public function getLogoutRedirectUrl(?string $postLogoutRedirectUri = null): string
    {
        $postLogoutRedirectUri = $postLogoutRedirectUri ?? url('/admin/login');

        $params = http_build_query([
            'client_id' => $this->config['client_id'],
            'post_logout_redirect_uri' => $postLogoutRedirectUri,
        ]);

        return $this->client->getLogoutEndpoint().'?'.$params;
    }
}
