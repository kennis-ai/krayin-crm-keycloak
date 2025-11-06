<?php

namespace Webkul\KeycloakSSO\Listeners;

use Illuminate\Support\Facades\Log;
use Webkul\KeycloakSSO\Events\KeycloakLoginSuccessful;

/**
 * SyncKeycloakUser Listener
 *
 * Handles post-login actions after successful Keycloak authentication.
 * This listener is fired after the user has been authenticated and provisioned.
 *
 * Note: User provisioning and role sync are already handled by UserProvisioningService
 * in the controller before this event is fired. This listener is for additional
 * actions like logging, analytics, notifications, or custom business logic.
 */
class SyncKeycloakUser
{
    /**
     * Handle the event.
     *
     * This method is called after successful Keycloak login.
     * Use this for:
     * - Logging successful authentications
     * - Sending notifications
     * - Tracking analytics
     * - Custom post-login actions
     * - Third-party integrations
     *
     * @param  \Webkul\KeycloakSSO\Events\KeycloakLoginSuccessful  $event
     * @return void
     */
    public function handle(KeycloakLoginSuccessful $event)
    {
        $user = $event->user;
        $keycloakData = $event->keycloakData;

        // Log successful login
        Log::info('Keycloak login successful - Event fired', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'keycloak_id' => $keycloakData['sub'] ?? null,
            'preferred_username' => $keycloakData['preferred_username'] ?? null,
            'email_verified' => $keycloakData['email_verified'] ?? null,
        ]);

        // Track last login time
        $this->updateLastLogin($user);

        // Additional custom actions can be added here
        // Examples:
        // - Send welcome email for first-time logins
        // - Update user analytics
        // - Sync with third-party services
        // - Trigger webhooks
        // - Log to analytics platforms
    }

    /**
     * Update user's last login timestamp.
     *
     * @param  mixed  $user
     * @return void
     */
    protected function updateLastLogin($user): void
    {
        try {
            // Check if user model has last_login_at column
            if (method_exists($user, 'getAttribute') &&
                array_key_exists('last_login_at', $user->getAttributes())) {
                $user->last_login_at = now();
                $user->save();

                Log::debug('Updated last login timestamp', [
                    'user_id' => $user->id,
                    'last_login_at' => $user->last_login_at,
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail - don't break login flow if this fails
            Log::warning('Failed to update last login timestamp', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
