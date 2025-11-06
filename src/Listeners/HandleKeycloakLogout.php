<?php

namespace Webkul\KeycloakSSO\Listeners;

use Illuminate\Support\Facades\Log;
use Webkul\KeycloakSSO\Events\KeycloakLogoutSuccessful;

/**
 * HandleKeycloakLogout Listener
 *
 * Handles post-logout actions after successful Keycloak logout.
 * This listener is fired after the user has been logged out from both
 * the application and Keycloak.
 *
 * Note: Session cleanup and token revocation are already handled by the
 * KeycloakAuthController before this event is fired. This listener is for
 * additional cleanup actions or custom business logic.
 */
class HandleKeycloakLogout
{
    /**
     * Handle the event.
     *
     * This method is called after successful Keycloak logout.
     * Use this for:
     * - Logging logout events
     * - Additional cleanup actions
     * - Tracking analytics
     * - Custom post-logout actions
     * - Third-party integrations
     * - Notification sending
     *
     * @param  \Webkul\KeycloakSSO\Events\KeycloakLogoutSuccessful  $event
     * @return void
     */
    public function handle(KeycloakLogoutSuccessful $event)
    {
        $user = $event->user;

        // Log successful logout
        Log::info('Keycloak logout successful - Event fired', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'logged_out_at' => now()->toDateTimeString(),
        ]);

        // Perform additional cleanup
        $this->cleanupUserSessions($user);

        // Additional custom actions can be added here
        // Examples:
        // - Clear user-specific caches
        // - Revoke API tokens
        // - Update session tracking
        // - Log to analytics platforms
        // - Send logout notifications
        // - Trigger webhooks
        // - Clean temporary files
    }

    /**
     * Clean up any additional user sessions or cached data.
     *
     * This method can be extended to handle custom session cleanup logic
     * that may be needed for your application.
     *
     * @param  mixed  $user
     * @return void
     */
    protected function cleanupUserSessions($user): void
    {
        try {
            // Additional session cleanup can be implemented here
            // Examples:
            // - Clear Redis session data
            // - Revoke remember tokens
            // - Clear user-specific cache
            // - Update online status

            Log::debug('User session cleanup completed', [
                'user_id' => $user->id,
            ]);

            // Example: Clear user cache (if using cache)
            if (config('cache.default') !== 'array') {
                cache()->forget("user.{$user->id}.session");
                cache()->forget("user.{$user->id}.keycloak_data");
            }
        } catch (\Exception $e) {
            // Silently fail - don't break logout flow if cleanup fails
            Log::warning('Failed to complete session cleanup', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
