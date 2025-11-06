<?php

namespace Webkul\KeycloakSSO\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webkul\KeycloakSSO\Exceptions\KeycloakConnectionException;
use Webkul\KeycloakSSO\Exceptions\KeycloakAuthenticationException;
use Webkul\KeycloakSSO\Exceptions\KeycloakUserProvisioningException;
use Webkul\KeycloakSSO\Exceptions\KeycloakTokenExpiredException;
use Webkul\KeycloakSSO\Helpers\ErrorHandler;

/**
 * ErrorHandler Test
 *
 * Basic tests for error handling functionality.
 */
class ErrorHandlerTest extends TestCase
{
    /**
     * Test that ErrorHandler can get user-friendly messages for different exception types.
     */
    public function test_get_user_message_for_connection_exception(): void
    {
        $exception = new KeycloakConnectionException('Connection failed');
        $message = ErrorHandler::getUserMessage($exception);

        $this->assertIsString($message);
        $this->assertNotEmpty($message);
    }

    /**
     * Test that ErrorHandler can get user-friendly messages for authentication exception.
     */
    public function test_get_user_message_for_authentication_exception(): void
    {
        $exception = new KeycloakAuthenticationException('Authentication failed');
        $message = ErrorHandler::getUserMessage($exception);

        $this->assertIsString($message);
        $this->assertNotEmpty($message);
    }

    /**
     * Test that ErrorHandler can get user-friendly messages for user provisioning exception.
     */
    public function test_get_user_message_for_user_provisioning_exception(): void
    {
        $exception = KeycloakUserProvisioningException::creationFailed('test@example.com');
        $message = ErrorHandler::getUserMessage($exception);

        $this->assertIsString($message);
        $this->assertNotEmpty($message);
    }

    /**
     * Test that ErrorHandler can get user-friendly messages for token expired exception.
     */
    public function test_get_user_message_for_token_expired_exception(): void
    {
        $exception = KeycloakTokenExpiredException::accessToken();
        $message = ErrorHandler::getUserMessage($exception);

        $this->assertIsString($message);
        $this->assertNotEmpty($message);
    }

    /**
     * Test that ErrorHandler sanitizes sensitive data in log data.
     */
    public function test_sanitize_log_data(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'secret123',
            'access_token' => 'token123',
            'client_secret' => 'secret456',
            'normal_field' => 'normal_value',
        ];

        $reflection = new \ReflectionClass(ErrorHandler::class);
        $method = $reflection->getMethod('sanitizeLogData');
        $method->setAccessible(true);

        $sanitized = $method->invoke(null, $data);

        $this->assertEquals('test@example.com', $sanitized['email']);
        $this->assertEquals('***REDACTED***', $sanitized['password']);
        $this->assertEquals('***REDACTED***', $sanitized['access_token']);
        $this->assertEquals('***REDACTED***', $sanitized['client_secret']);
        $this->assertEquals('normal_value', $sanitized['normal_field']);
    }

    /**
     * Test that shouldFallbackToLocalAuth returns correct value based on config.
     */
    public function test_should_fallback_to_local_auth(): void
    {
        $result = ErrorHandler::shouldFallbackToLocalAuth();

        $this->assertIsBool($result);
    }

    /**
     * Test that retry handler works with successful callback.
     */
    public function test_retry_with_successful_callback(): void
    {
        $counter = 0;

        $result = ErrorHandler::retry(function () use (&$counter) {
            $counter++;
            return 'success';
        }, 3, 10);

        $this->assertEquals('success', $result);
        $this->assertEquals(1, $counter, 'Callback should be called only once on success');
    }

    /**
     * Test that retry handler retries on failure.
     */
    public function test_retry_with_failures_then_success(): void
    {
        $counter = 0;

        $result = ErrorHandler::retry(function () use (&$counter) {
            $counter++;
            if ($counter < 3) {
                throw new \Exception('Failed attempt ' . $counter);
            }
            return 'success';
        }, 3, 10);

        $this->assertEquals('success', $result);
        $this->assertEquals(3, $counter, 'Callback should be retried until success');
    }

    /**
     * Test that retry handler throws exception after max attempts.
     */
    public function test_retry_throws_after_max_attempts(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Always fails');

        $counter = 0;

        ErrorHandler::retry(function () use (&$counter) {
            $counter++;
            throw new \Exception('Always fails');
        }, 3, 10);
    }

    /**
     * Test exception factory methods.
     */
    public function test_exception_factory_methods(): void
    {
        // Connection exceptions
        $timeout = KeycloakConnectionException::timeout();
        $this->assertInstanceOf(KeycloakConnectionException::class, $timeout);
        $this->assertEquals(504, $timeout->getCode());

        $unreachable = KeycloakConnectionException::unreachable();
        $this->assertInstanceOf(KeycloakConnectionException::class, $unreachable);
        $this->assertEquals(503, $unreachable->getCode());

        // Token expired exceptions
        $accessToken = KeycloakTokenExpiredException::accessToken();
        $this->assertInstanceOf(KeycloakTokenExpiredException::class, $accessToken);

        $refreshToken = KeycloakTokenExpiredException::refreshToken();
        $this->assertInstanceOf(KeycloakTokenExpiredException::class, $refreshToken);

        // User provisioning exceptions
        $creationFailed = KeycloakUserProvisioningException::creationFailed('test@example.com');
        $this->assertInstanceOf(KeycloakUserProvisioningException::class, $creationFailed);
        $this->assertStringContainsString('test@example.com', $creationFailed->getMessage());

        $missingField = KeycloakUserProvisioningException::missingRequiredField('email');
        $this->assertInstanceOf(KeycloakUserProvisioningException::class, $missingField);
        $this->assertStringContainsString('email', $missingField->getMessage());
    }
}
