<?php

namespace Webkul\KeycloakSSO\Helpers;

use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;
use Webkul\KeycloakSSO\Exceptions\KeycloakException;

/**
 * ErrorHandler
 *
 * Centralized error handling helper for Keycloak SSO operations.
 * Provides logging, error message formatting, and debug information.
 */
class ErrorHandler
{
    /**
     * Handle and log an exception.
     *
     * @param  Throwable  $exception
     * @param  string  $context
     * @param  array  $additionalData
     * @return void
     */
    public static function handle(Throwable $exception, string $context, array $additionalData = []): void
    {
        $config = config('keycloak');
        $debug = $config['debug'] ?? false;
        $logStackTraces = $config['error_handling']['log_stack_traces'] ?? true;

        // Prepare log data
        $logData = [
            'context' => $context,
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ];

        // Add additional data
        if (!empty($additionalData)) {
            $logData['additional_data'] = self::sanitizeLogData($additionalData);
        }

        // Add stack trace if enabled
        if ($logStackTraces) {
            $logData['trace'] = $exception->getTraceAsString();
        }

        // Determine log level based on exception type
        $logLevel = self::determineLogLevel($exception);

        // Log the error
        self::log($logLevel, "Keycloak Error: {$context}", $logData);

        // In debug mode, also log to stderr for immediate visibility
        if ($debug) {
            error_log("[Keycloak Debug] {$context}: {$exception->getMessage()}");
        }
    }

    /**
     * Get user-friendly error message.
     *
     * @param  Throwable  $exception
     * @param  bool  $includeDebugInfo
     * @return string
     */
    public static function getUserMessage(Throwable $exception, bool $includeDebugInfo = false): string
    {
        $config = config('keycloak');
        $showDetails = $config['error_handling']['show_details'] ?? false;
        $debug = $config['debug'] ?? false;

        // Get base message from translation key
        $message = self::getTranslatedMessage($exception);

        // Add debug information if enabled
        if (($includeDebugInfo || $showDetails || $debug) && $exception instanceof Exception) {
            $debugInfo = trans('keycloak::errors.debug.exception_details', [
                'exception' => get_class($exception),
            ]);
            $message .= "\n\n" . $debugInfo;

            if ($exception->getCode()) {
                $message .= "\n" . trans('keycloak::errors.debug.error_code', [
                    'code' => $exception->getCode(),
                ]);
            }
        }

        return $message;
    }

    /**
     * Get translated error message based on exception type.
     *
     * @param  Throwable  $exception
     * @return string
     */
    protected static function getTranslatedMessage(Throwable $exception): string
    {
        // Map exception classes to translation keys
        $exceptionClass = get_class($exception);

        $translationMap = [
            'Webkul\KeycloakSSO\Exceptions\KeycloakConnectionException' => 'keycloak::errors.connection.failed',
            'Webkul\KeycloakSSO\Exceptions\KeycloakAuthenticationException' => 'keycloak::errors.authentication.failed',
            'Webkul\KeycloakSSO\Exceptions\KeycloakTokenException' => 'keycloak::errors.token.invalid',
            'Webkul\KeycloakSSO\Exceptions\KeycloakTokenExpiredException' => 'keycloak::errors.token.expired',
            'Webkul\KeycloakSSO\Exceptions\KeycloakUserProvisioningException' => 'keycloak::errors.provisioning.failed',
            'Webkul\KeycloakSSO\Exceptions\KeycloakConfigurationException' => 'keycloak::errors.configuration.invalid',
        ];

        // Check if we have a specific translation for this exception
        if (isset($translationMap[$exceptionClass])) {
            return trans($translationMap[$exceptionClass]);
        }

        // Check if exception message contains specific keywords
        $message = strtolower($exception->getMessage());

        if (str_contains($message, 'timeout')) {
            return trans('keycloak::errors.connection.timeout');
        }

        if (str_contains($message, 'unreachable') || str_contains($message, 'connection refused')) {
            return trans('keycloak::errors.connection.unreachable');
        }

        if (str_contains($message, 'expired')) {
            return trans('keycloak::errors.token.expired');
        }

        if (str_contains($message, 'access denied') || str_contains($message, 'forbidden')) {
            return trans('keycloak::errors.authentication.access_denied');
        }

        // Default generic error message
        return trans('keycloak::errors.generic.unknown');
    }

    /**
     * Determine appropriate log level based on exception type.
     *
     * @param  Throwable  $exception
     * @return string
     */
    protected static function determineLogLevel(Throwable $exception): string
    {
        $exceptionClass = get_class($exception);

        // Critical errors that require immediate attention
        $criticalExceptions = [
            'Webkul\KeycloakSSO\Exceptions\KeycloakConfigurationException',
        ];

        // Errors that are expected in certain scenarios
        $warningExceptions = [
            'Webkul\KeycloakSSO\Exceptions\KeycloakConnectionException',
        ];

        if (in_array($exceptionClass, $criticalExceptions)) {
            return 'critical';
        }

        if (in_array($exceptionClass, $warningExceptions)) {
            return 'warning';
        }

        // Check error codes
        $code = $exception->getCode();

        if ($code >= 500) {
            return 'error';
        }

        if ($code >= 400 && $code < 500) {
            return 'warning';
        }

        return 'error';
    }

    /**
     * Log a message with given level.
     *
     * @param  string  $level
     * @param  string  $message
     * @param  array  $context
     * @return void
     */
    protected static function log(string $level, string $message, array $context = []): void
    {
        $config = config('keycloak');
        $loggingEnabled = $config['logging']['enabled'] ?? true;

        if (!$loggingEnabled) {
            return;
        }

        $channel = $config['logging']['channel'] ?? 'stack';
        $minLevel = $config['logging']['level'] ?? 'info';

        // Check if we should log based on minimum level
        $levels = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3, 'critical' => 4];
        $currentLevelValue = $levels[$level] ?? 3;
        $minLevelValue = $levels[$minLevel] ?? 1;

        if ($currentLevelValue < $minLevelValue) {
            return;
        }

        // Log to specified channel
        Log::channel($channel)->$level($message, $context);
    }

    /**
     * Sanitize log data to remove sensitive information.
     *
     * @param  array  $data
     * @return array
     */
    protected static function sanitizeLogData(array $data): array
    {
        $sensitiveKeys = [
            'password',
            'client_secret',
            'access_token',
            'refresh_token',
            'token',
            'secret',
            'authorization',
            'api_key',
        ];

        $sanitized = [];

        foreach ($data as $key => $value) {
            $lowerKey = strtolower($key);

            // Check if key contains sensitive information
            $isSensitive = false;
            foreach ($sensitiveKeys as $sensitiveKey) {
                if (str_contains($lowerKey, $sensitiveKey)) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                $sanitized[$key] = '***REDACTED***';
            } elseif (is_array($value)) {
                $sanitized[$key] = self::sanitizeLogData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Check if Keycloak is available and fallback is enabled.
     *
     * @return bool
     */
    public static function shouldFallbackToLocalAuth(): bool
    {
        $config = config('keycloak');

        return $config['fallback_on_error'] ?? true;
    }

    /**
     * Create a retry handler with exponential backoff.
     *
     * @param  callable  $callback
     * @param  int|null  $maxAttempts
     * @param  int|null  $baseDelay
     * @return mixed
     *
     * @throws Exception
     */
    public static function retry(callable $callback, ?int $maxAttempts = null, ?int $baseDelay = null)
    {
        $config = config('keycloak.error_handling', []);
        $maxAttempts = $maxAttempts ?? ($config['max_retries'] ?? 3);
        $baseDelay = $baseDelay ?? ($config['retry_delay'] ?? 1000);
        $useExponentialBackoff = $config['exponential_backoff'] ?? true;

        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxAttempts) {
            try {
                return $callback();
            } catch (Exception $e) {
                $lastException = $e;
                $attempt++;

                // Don't retry on the last attempt
                if ($attempt >= $maxAttempts) {
                    break;
                }

                // Calculate delay with exponential backoff if enabled
                $delay = $useExponentialBackoff
                    ? $baseDelay * pow(2, $attempt - 1)
                    : $baseDelay;

                // Add jitter (random ±20%)
                $jitter = $delay * (0.8 + (mt_rand() / mt_getrandmax()) * 0.4);

                self::log('info', "Retry attempt {$attempt}/{$maxAttempts} after {$jitter}ms", [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ]);

                // Sleep for delay (convert to microseconds)
                usleep((int) ($jitter * 1000));
            }
        }

        throw $lastException;
    }
}
