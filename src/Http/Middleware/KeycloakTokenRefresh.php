<?php

namespace Webkul\KeycloakSSO\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Webkul\KeycloakSSO\Exceptions\KeycloakTokenException;
use Webkul\KeycloakSSO\Services\KeycloakService;

/**
 * KeycloakTokenRefresh Middleware
 *
 * Automatically refreshes expired Keycloak tokens before they become invalid.
 * This middleware checks token expiration and refreshes tokens proactively
 * to ensure seamless user experience without unexpected logouts.
 */
class KeycloakTokenRefresh
{
    /**
     * Keycloak service instance.
     *
     * @var KeycloakService
     */
    protected KeycloakService $keycloakService;

    /**
     * Number of seconds before token expiration to trigger refresh.
     * Default: 300 seconds (5 minutes)
     *
     * @var int
     */
    protected int $refreshThreshold;

    /**
     * Create a new middleware instance.
     *
     * @param  KeycloakService  $keycloakService
     */
    public function __construct(KeycloakService $keycloakService)
    {
        $this->keycloakService = $keycloakService;
        $this->refreshThreshold = config('keycloak.token_refresh_threshold', 300);
    }

    /**
     * Handle an incoming request.
     *
     * This middleware performs the following:
     * 1. Checks if user is authenticated and is a Keycloak user
     * 2. Checks if token is expired or about to expire
     * 3. Attempts to refresh the token using refresh token
     * 4. Updates session with new tokens
     * 5. Handles refresh failures gracefully
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $guard = null)
    {
        // Check if Keycloak SSO is enabled
        if (! $this->keycloakService->isEnabled()) {
            return $next($request);
        }

        // Get the authenticated user
        $user = Auth::guard($guard ?? 'user')->user();

        // If no user or not a Keycloak user, continue
        if (! $user || ! $user->isKeycloakUser()) {
            return $next($request);
        }

        // Check if token needs refresh
        if (! $this->shouldRefreshToken($user)) {
            Log::debug('KeycloakTokenRefresh: Token does not need refresh', [
                'user_id' => $user->id,
            ]);

            return $next($request);
        }

        // Attempt to refresh token
        try {
            $this->refreshUserToken($user);

            Log::info('KeycloakTokenRefresh: Token refreshed successfully', [
                'user_id' => $user->id,
            ]);

            return $next($request);
        } catch (KeycloakTokenException $e) {
            Log::error('KeycloakTokenRefresh: Failed to refresh token', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            // Check if we should logout or continue
            if (config('keycloak.logout_on_refresh_failure', true)) {
                return $this->handleRefreshFailure($request, $guard);
            }

            // Continue without refresh (will likely fail on next token validation)
            return $next($request);
        } catch (\Exception $e) {
            Log::error('KeycloakTokenRefresh: Unexpected error during token refresh', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            // Continue without refresh
            return $next($request);
        }
    }

    /**
     * Check if token should be refreshed.
     *
     * Returns true if token is expired or will expire within the refresh threshold.
     *
     * @param  mixed  $user
     * @return bool
     */
    protected function shouldRefreshToken($user): bool
    {
        // Check if user has token expiration time
        if (! method_exists($user, 'getKeycloakTokenExpiration')) {
            Log::debug('KeycloakTokenRefresh: User model does not support token expiration');
            return false;
        }

        $tokenExpiration = $user->getKeycloakTokenExpiration();

        // If no expiration time, don't refresh
        if (! $tokenExpiration) {
            Log::debug('KeycloakTokenRefresh: No token expiration time set', [
                'user_id' => $user->id,
            ]);
            return false;
        }

        // Check if token is expired or about to expire
        $now = now();
        $expiresAt = $tokenExpiration;

        if ($now->greaterThanOrEqualTo($expiresAt)) {
            Log::info('KeycloakTokenRefresh: Token has expired', [
                'user_id' => $user->id,
                'expired_at' => $expiresAt->toDateTimeString(),
            ]);
            return true;
        }

        // Check if token will expire within threshold
        $secondsUntilExpiry = $now->diffInSeconds($expiresAt, false);

        if ($secondsUntilExpiry <= $this->refreshThreshold) {
            Log::info('KeycloakTokenRefresh: Token expiring soon, triggering refresh', [
                'user_id' => $user->id,
                'seconds_until_expiry' => $secondsUntilExpiry,
                'threshold' => $this->refreshThreshold,
            ]);
            return true;
        }

        return false;
    }

    /**
     * Refresh user's Keycloak token.
     *
     * @param  mixed  $user
     * @return void
     *
     * @throws KeycloakTokenException
     */
    protected function refreshUserToken($user): void
    {
        // Get refresh token
        $refreshToken = $user->getKeycloakRefreshToken();

        if (! $refreshToken) {
            throw KeycloakTokenException::refreshFailed('No refresh token available');
        }

        // Refresh token with Keycloak
        $tokens = $this->keycloakService->refreshToken($refreshToken);

        // Update user's tokens
        $user->setKeycloakRefreshToken($tokens['refresh_token']);
        $user->updateKeycloakTokenExpiration($tokens['expires_in']);
        $user->save();

        // Update session with new access token
        session(['keycloak_access_token' => $tokens['access_token']]);

        Log::info('KeycloakTokenRefresh: Updated user tokens', [
            'user_id' => $user->id,
            'expires_in' => $tokens['expires_in'],
        ]);
    }

    /**
     * Handle token refresh failure.
     *
     * Logs out the user and redirects to login.
     *
     * @param  Request  $request
     * @param  string|null  $guard
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function handleRefreshFailure(Request $request, ?string $guard = null)
    {
        $user = Auth::guard($guard ?? 'user')->user();

        if ($user) {
            Log::info('KeycloakTokenRefresh: Logging out user due to refresh failure', [
                'user_id' => $user->id,
            ]);

            // Clear Keycloak data
            if (method_exists($user, 'clearKeycloakData')) {
                $user->clearKeycloakData();
                $user->save();
            }
        }

        // Logout
        Auth::guard($guard ?? 'user')->logout();

        // Clear session
        session()->forget(['keycloak_access_token', 'keycloak_state']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Store intended URL
        if (! $request->expectsJson()) {
            session(['url.intended' => $request->url()]);
        }

        return redirect()->route('admin.session.create')
            ->with('error', trans('keycloak-sso::auth.token_refresh_failed'));
    }
}
