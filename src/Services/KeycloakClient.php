<?php

namespace Webkul\KeycloakSSO\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Webkul\KeycloakSSO\Exceptions\KeycloakAuthenticationException;
use Webkul\KeycloakSSO\Exceptions\KeycloakConnectionException;
use Webkul\KeycloakSSO\Exceptions\KeycloakException;
use Webkul\KeycloakSSO\Exceptions\KeycloakTokenException;
use Webkul\KeycloakSSO\Helpers\ErrorHandler;

/**
 * HTTP client for Keycloak API communication
 */
class KeycloakClient
{
    /**
     * Guzzle HTTP client instance
     *
     * @var \GuzzleHttp\Client
     */
    protected Client $client;

    /**
     * Keycloak base URL
     *
     * @var string
     */
    protected string $baseUrl;

    /**
     * Keycloak realm
     *
     * @var string
     */
    protected string $realm;

    /**
     * HTTP timeout in seconds
     *
     * @var int
     */
    protected int $timeout;

    /**
     * Create a new Keycloak client instance.
     *
     * @param  string  $baseUrl
     * @param  string  $realm
     * @param  int  $timeout
     */
    public function __construct(string $baseUrl, string $realm, int $timeout = 30)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->realm = $realm;
        $this->timeout = $timeout;

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'http_errors' => false,
            'verify' => true,
        ]);
    }

    /**
     * Get the authorization endpoint URL.
     *
     * @return string
     */
    public function getAuthorizationEndpoint(): string
    {
        return $this->getRealmUrl().'/protocol/openid-connect/auth';
    }

    /**
     * Get the token endpoint URL.
     *
     * @return string
     */
    public function getTokenEndpoint(): string
    {
        return $this->getRealmUrl().'/protocol/openid-connect/token';
    }

    /**
     * Get the userinfo endpoint URL.
     *
     * @return string
     */
    public function getUserInfoEndpoint(): string
    {
        return $this->getRealmUrl().'/protocol/openid-connect/userinfo';
    }

    /**
     * Get the logout endpoint URL.
     *
     * @return string
     */
    public function getLogoutEndpoint(): string
    {
        return $this->getRealmUrl().'/protocol/openid-connect/logout';
    }

    /**
     * Get the token introspection endpoint URL.
     *
     * @return string
     */
    public function getIntrospectEndpoint(): string
    {
        return $this->getRealmUrl().'/protocol/openid-connect/token/introspect';
    }

    /**
     * Get the realm base URL.
     *
     * @return string
     */
    protected function getRealmUrl(): string
    {
        return "{$this->baseUrl}/realms/{$this->realm}";
    }

    /**
     * Exchange authorization code for tokens.
     *
     * @param  string  $code
     * @param  string  $clientId
     * @param  string  $clientSecret
     * @param  string  $redirectUri
     * @return array
     *
     * @throws KeycloakAuthenticationException
     * @throws KeycloakConnectionException
     */
    public function getTokens(string $code, string $clientId, string $clientSecret, string $redirectUri): array
    {
        return $this->requestTokens([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
        ]);
    }

    /**
     * Refresh an access token using a refresh token.
     *
     * @param  string  $refreshToken
     * @param  string  $clientId
     * @param  string  $clientSecret
     * @return array
     *
     * @throws KeycloakTokenException
     * @throws KeycloakConnectionException
     */
    public function refreshToken(string $refreshToken, string $clientId, string $clientSecret): array
    {
        try {
            return $this->requestTokens([
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]);
        } catch (KeycloakAuthenticationException $e) {
            throw KeycloakTokenException::refreshFailed();
        }
    }

    /**
     * Request tokens from Keycloak token endpoint.
     *
     * @param  array  $params
     * @return array
     *
     * @throws KeycloakAuthenticationException
     * @throws KeycloakConnectionException
     */
    protected function requestTokens(array $params): array
    {
        try {
            return ErrorHandler::retry(function () use ($params) {
                $response = $this->client->post($this->getTokenEndpoint(), [
                    'form_params' => $params,
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                ]);

                $statusCode = $response->getStatusCode();
                $body = json_decode($response->getBody()->getContents(), true);

                if ($statusCode !== 200) {
                    $errorMessage = $body['error_description'] ?? $body['error'] ?? 'Token request failed';
                    throw new KeycloakAuthenticationException($errorMessage, $statusCode);
                }

                return $body;
            });
        } catch (ConnectException $e) {
            ErrorHandler::handle($e, 'Keycloak connection failed during token request', [
                'endpoint' => $this->getTokenEndpoint(),
            ]);
            throw KeycloakConnectionException::unreachable();
        } catch (RequestException $e) {
            ErrorHandler::handle($e, 'Keycloak token request failed', [
                'endpoint' => $this->getTokenEndpoint(),
            ]);
            throw new KeycloakAuthenticationException(
                'Token request failed: '.$e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get user information from Keycloak.
     *
     * @param  string  $accessToken
     * @return array
     *
     * @throws KeycloakAuthenticationException
     * @throws KeycloakConnectionException
     */
    public function getUserInfo(string $accessToken): array
    {
        try {
            return ErrorHandler::retry(function () use ($accessToken) {
                $response = $this->client->get($this->getUserInfoEndpoint(), [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer '.$accessToken,
                    ],
                ]);

                $statusCode = $response->getStatusCode();
                $body = json_decode($response->getBody()->getContents(), true);

                if ($statusCode !== 200) {
                    $errorMessage = $body['error_description'] ?? $body['error'] ?? 'Failed to retrieve user info';
                    throw new KeycloakAuthenticationException($errorMessage, $statusCode);
                }

                return $body;
            });
        } catch (ConnectException $e) {
            ErrorHandler::handle($e, 'Keycloak connection failed during user info retrieval', [
                'endpoint' => $this->getUserInfoEndpoint(),
            ]);
            throw KeycloakConnectionException::unreachable();
        } catch (RequestException $e) {
            ErrorHandler::handle($e, 'Keycloak user info request failed', [
                'endpoint' => $this->getUserInfoEndpoint(),
            ]);
            throw new KeycloakAuthenticationException(
                'User info request failed: '.$e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Introspect a token to check if it's valid.
     *
     * @param  string  $token
     * @param  string  $clientId
     * @param  string  $clientSecret
     * @return array
     *
     * @throws KeycloakException
     * @throws KeycloakConnectionException
     */
    public function introspectToken(string $token, string $clientId, string $clientSecret): array
    {
        try {
            return ErrorHandler::retry(function () use ($token, $clientId, $clientSecret) {
                $response = $this->client->post($this->getIntrospectEndpoint(), [
                    'form_params' => [
                        'token' => $token,
                        'client_id' => $clientId,
                        'client_secret' => $clientSecret,
                    ],
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                ]);

                $statusCode = $response->getStatusCode();
                $body = json_decode($response->getBody()->getContents(), true);

                if ($statusCode !== 200) {
                    $errorMessage = $body['error_description'] ?? $body['error'] ?? 'Token introspection failed';
                    throw new KeycloakException($errorMessage, $statusCode);
                }

                return $body;
            });
        } catch (ConnectException $e) {
            ErrorHandler::handle($e, 'Keycloak connection failed during token introspection', [
                'endpoint' => $this->getIntrospectEndpoint(),
            ]);
            throw KeycloakConnectionException::unreachable();
        } catch (RequestException $e) {
            ErrorHandler::handle($e, 'Keycloak token introspection failed', [
                'endpoint' => $this->getIntrospectEndpoint(),
            ]);
            throw new KeycloakException(
                'Token introspection failed: '.$e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Logout from Keycloak (revoke refresh token).
     *
     * @param  string  $refreshToken
     * @param  string  $clientId
     * @param  string  $clientSecret
     * @return bool
     *
     * @throws KeycloakConnectionException
     */
    public function logout(string $refreshToken, string $clientId, string $clientSecret): bool
    {
        try {
            $response = $this->client->post($this->getLogoutEndpoint(), [
                'form_params' => [
                    'refresh_token' => $refreshToken,
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();

            // 204 No Content is the standard success response for logout
            if ($statusCode === 204 || $statusCode === 200) {
                return true;
            }

            // Log the failure but don't throw exception for logout
            // as the session might already be expired
            Log::warning('Keycloak logout response was not successful', [
                'status_code' => $statusCode,
                'body' => $response->getBody()->getContents(),
            ]);

            return false;
        } catch (ConnectException $e) {
            ErrorHandler::handle($e, 'Keycloak connection failed during logout', [
                'endpoint' => $this->getLogoutEndpoint(),
            ]);
            throw KeycloakConnectionException::unreachable();
        } catch (RequestException $e) {
            ErrorHandler::handle($e, 'Keycloak logout request failed', [
                'endpoint' => $this->getLogoutEndpoint(),
            ]);

            // Don't throw exception for logout failures
            return false;
        }
    }

    /**
     * Get the configured realm name.
     *
     * @return string
     */
    public function getRealm(): string
    {
        return $this->realm;
    }

    /**
     * Get the configured base URL.
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
