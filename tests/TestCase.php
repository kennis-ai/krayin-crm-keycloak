<?php

namespace Webkul\KeycloakSSO\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base test case for Keycloak SSO tests.
 *
 * Provides common testing utilities and helpers.
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Clean up the testing environment.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Get mock Keycloak user data.
     *
     * @param  array  $overrides
     * @return array
     */
    protected function getMockKeycloakUser(array $overrides = []): array
    {
        return array_merge([
            'sub' => 'test-user-id-123',
            'email' => 'test@example.com',
            'email_verified' => true,
            'name' => 'Test User',
            'given_name' => 'Test',
            'family_name' => 'User',
            'preferred_username' => 'testuser',
            'realm_access' => [
                'roles' => ['user', 'test-role'],
            ],
        ], $overrides);
    }

    /**
     * Get mock Keycloak tokens.
     *
     * @param  array  $overrides
     * @return array
     */
    protected function getMockTokens(array $overrides = []): array
    {
        return array_merge([
            'access_token' => 'mock-access-token-' . uniqid(),
            'refresh_token' => 'mock-refresh-token-' . uniqid(),
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'refresh_expires_in' => 86400,
        ], $overrides);
    }

    /**
     * Get mock Keycloak configuration.
     *
     * @param  array  $overrides
     * @return array
     */
    protected function getMockConfig(array $overrides = []): array
    {
        return array_merge([
            'enabled' => true,
            'base_url' => 'https://keycloak.example.com',
            'realm' => 'test-realm',
            'client_id' => 'test-client',
            'client_secret' => 'test-secret',
            'redirect_uri' => 'https://example.com/callback',
            'auto_provision_users' => true,
            'sync_user_data' => true,
            'enable_role_mapping' => true,
            'allow_local_auth' => true,
            'fallback_on_error' => true,
            'role_mapping' => [
                'admin' => 'Administrator',
                'user' => 'Sales Agent',
            ],
            'http_timeout' => 30,
        ], $overrides);
    }

    /**
     * Assert that an array has specific keys.
     *
     * @param  array  $keys
     * @param  array  $array
     * @param  string  $message
     */
    protected function assertArrayHasKeys(array $keys, array $array, string $message = ''): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array, $message ?: "Array is missing key: {$key}");
        }
    }
}
