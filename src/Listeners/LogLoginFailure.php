<?php

namespace Webkul\KeycloakSSO\Listeners;

use Illuminate\Support\Facades\Log;
use Webkul\KeycloakSSO\Events\KeycloakLoginFailed;

/**
 * LogLoginFailure Listener
 *
 * Handles actions after Keycloak authentication failure.
 * This listener is fired when the OAuth2/OpenID Connect authentication
 * flow fails at any point.
 *
 * Use cases:
 * - Security logging and monitoring
 * - Failed login tracking for rate limiting
 * - Notification of authentication issues
 * - Analytics and metrics
 */
class LogLoginFailure
{
    /**
     * Handle the event.
     *
     * This method is called after Keycloak authentication fails.
     * Use this for:
     * - Logging failed authentication attempts
     * - Security monitoring
     * - Failed login rate limiting
     * - Sending alerts for suspicious activity
     * - Tracking authentication metrics
     * - Custom error handling
     *
     * @param  \Webkul\KeycloakSSO\Events\KeycloakLoginFailed  $event
     * @return void
     */
    public function handle(KeycloakLoginFailed $event)
    {
        $exception = $event->exception;
        $keycloakData = $event->keycloakData;

        // Log the authentication failure
        Log::warning('Keycloak login failed - Event fired', [
            'exception_type' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'exception_code' => $exception->getCode(),
            'keycloak_data' => $this->sanitizeKeycloakData($keycloakData),
            'timestamp' => now()->toDateTimeString(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Track failed attempts for rate limiting
        $this->trackFailedAttempt();

        // Additional custom actions can be added here
        // Examples:
        // - Send security alerts for suspicious patterns
        // - Update failed login counters
        // - Trigger captcha after multiple failures
        // - Send notifications to admins
        // - Log to external monitoring services
        // - Update security dashboards
    }

    /**
     * Sanitize Keycloak data for logging (remove sensitive information).
     *
     * @param  array|null  $keycloakData
     * @return array|null
     */
    protected function sanitizeKeycloakData(?array $keycloakData): ?array
    {
        if (! $keycloakData) {
            return null;
        }

        // Remove sensitive data that shouldn't be logged
        $sanitized = $keycloakData;

        // Remove tokens and sensitive claims
        $sensitiveKeys = [
            'access_token',
            'refresh_token',
            'id_token',
            'password',
            'secret',
        ];

        foreach ($sensitiveKeys as $key) {
            if (isset($sanitized[$key])) {
                $sanitized[$key] = '[REDACTED]';
            }
        }

        return $sanitized;
    }

    /**
     * Track failed login attempt for rate limiting.
     *
     * This can be extended to implement sophisticated rate limiting
     * or brute force protection mechanisms.
     *
     * @return void
     */
    protected function trackFailedAttempt(): void
    {
        try {
            $ipAddress = request()->ip();
            $cacheKey = "keycloak_failed_login:{$ipAddress}";

            // Increment failed attempt counter (expires after 15 minutes)
            $attempts = cache()->increment($cacheKey, 1);

            if (! $attempts || $attempts === 1) {
                // Set expiration on first attempt
                cache()->put($cacheKey, 1, now()->addMinutes(15));
            }

            // Log warning if too many attempts
            if ($attempts >= 5) {
                Log::warning('Multiple failed Keycloak login attempts detected', [
                    'ip_address' => $ipAddress,
                    'attempts' => $attempts,
                    'timeframe' => '15 minutes',
                ]);

                // Example: Send alert to admins
                // event(new SuspiciousActivityDetected($ipAddress, $attempts));
            }

            Log::debug('Failed login attempt tracked', [
                'ip_address' => $ipAddress,
                'attempts' => $attempts,
            ]);
        } catch (\Exception $e) {
            // Silently fail - don't break error handling if tracking fails
            Log::debug('Failed to track login attempt', [
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
