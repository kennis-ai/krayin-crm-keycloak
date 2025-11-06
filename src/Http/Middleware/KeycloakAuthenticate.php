<?php

namespace Webkul\KeycloakSSO\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Webkul\KeycloakSSO\Services\KeycloakService;

/**
 * KeycloakAuthenticate Middleware
 *
 * Verifies Keycloak authentication and redirects to login if needed.
 * This middleware ensures that users authenticated via Keycloak have valid sessions.
 */
class KeycloakAuthenticate
{
    /**
     * Keycloak service instance.
     *
     * @var KeycloakService
     */
    protected KeycloakService $keycloakService;

    /**
     * Create a new middleware instance.
     *
     * @param  KeycloakService  $keycloakService
     */
    public function __construct(KeycloakService $keycloakService)
    {
        $this->keycloakService = $keycloakService;
    }

    /**
     * Handle an incoming request.
     *
     * This middleware performs the following checks:
     * 1. Verifies user is authenticated
     * 2. Checks if user is a Keycloak user
     * 3. Validates Keycloak token if available
     * 4. Redirects to login if authentication is invalid
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
            // Keycloak is disabled, let Laravel's default auth handle this
            return $next($request);
        }

        // Get the authenticated user
        $user = Auth::guard($guard ?? 'user')->user();

        // If no user is authenticated, redirect to login
        if (! $user) {
            Log::debug('KeycloakAuthenticate: No authenticated user, redirecting to login');

            return $this->redirectToLogin($request);
        }

        // If user is not a Keycloak user, continue (local auth)
        if (! $user->isKeycloakUser()) {
            Log::debug('KeycloakAuthenticate: User is not a Keycloak user, allowing access', [
                'user_id' => $user->id,
                'auth_provider' => $user->auth_provider,
            ]);

            return $next($request);
        }

        // Check if Keycloak token exists in session
        $accessToken = session('keycloak_access_token');

        if (! $accessToken) {
            Log::warning('KeycloakAuthenticate: Keycloak user without access token', [
                'user_id' => $user->id,
            ]);

            // Keycloak user without token - might be expired or session cleared
            // Logout and redirect to login
            return $this->handleInvalidSession($request, $guard);
        }

        // Validate token with Keycloak
        try {
            $isValid = $this->keycloakService->validateToken($accessToken);

            if (! $isValid) {
                Log::warning('KeycloakAuthenticate: Invalid Keycloak token', [
                    'user_id' => $user->id,
                ]);

                return $this->handleInvalidSession($request, $guard);
            }

            Log::debug('KeycloakAuthenticate: Token validated successfully', [
                'user_id' => $user->id,
            ]);

            // Token is valid, continue
            return $next($request);
        } catch (\Exception $e) {
            Log::error('KeycloakAuthenticate: Error validating token', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            // On validation error, check if we should allow access anyway
            if (config('keycloak.allow_access_on_validation_error', false)) {
                Log::info('KeycloakAuthenticate: Allowing access despite validation error', [
                    'user_id' => $user->id,
                ]);

                return $next($request);
            }

            // Otherwise, logout and redirect
            return $this->handleInvalidSession($request, $guard);
        }
    }

    /**
     * Handle invalid Keycloak session.
     *
     * Logs out the user and redirects to login.
     *
     * @param  Request  $request
     * @param  string|null  $guard
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function handleInvalidSession(Request $request, ?string $guard = null)
    {
        $user = Auth::guard($guard ?? 'user')->user();

        if ($user) {
            Log::info('KeycloakAuthenticate: Logging out user due to invalid session', [
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

        return $this->redirectToLogin($request);
    }

    /**
     * Redirect to login page with intended URL.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToLogin(Request $request)
    {
        // Store intended URL for redirect after login
        if (! $request->expectsJson()) {
            session(['url.intended' => $request->url()]);
        }

        // Check if we should redirect to Keycloak login or local login
        $redirectToKeycloak = config('keycloak.auto_redirect_to_keycloak', false);

        if ($redirectToKeycloak) {
            Log::debug('KeycloakAuthenticate: Auto-redirecting to Keycloak login');

            return redirect()->route('admin.keycloak.login');
        }

        Log::debug('KeycloakAuthenticate: Redirecting to local login');

        return redirect()->route('admin.session.create')
            ->with('warning', trans('keycloak-sso::auth.session_expired'));
    }
}
