<?php

namespace Webkul\KeycloakSSO\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Webkul\KeycloakSSO\Events\KeycloakLoginFailed;
use Webkul\KeycloakSSO\Events\KeycloakLoginSuccessful;
use Webkul\KeycloakSSO\Events\KeycloakLogoutSuccessful;
use Webkul\KeycloakSSO\Exceptions\KeycloakAuthenticationException;
use Webkul\KeycloakSSO\Exceptions\KeycloakConnectionException;
use Webkul\KeycloakSSO\Exceptions\KeycloakException;
use Webkul\KeycloakSSO\Exceptions\KeycloakUserProvisioningException;
use Webkul\KeycloakSSO\Helpers\ErrorHandler;
use Webkul\KeycloakSSO\Services\KeycloakService;
use Webkul\KeycloakSSO\Services\UserProvisioningService;

/**
 * KeycloakAuthController
 *
 * Handles Keycloak OAuth2/OpenID Connect authentication flow.
 * Manages login redirect, callback processing, and logout with Single Logout (SLO).
 */
class KeycloakAuthController extends Controller
{
    /**
     * Keycloak service instance.
     *
     * @var KeycloakService
     */
    protected KeycloakService $keycloakService;

    /**
     * User provisioning service instance.
     *
     * @var UserProvisioningService
     */
    protected UserProvisioningService $provisioningService;

    /**
     * Create a new controller instance.
     *
     * @param  KeycloakService  $keycloakService
     * @param  UserProvisioningService  $provisioningService
     */
    public function __construct(
        KeycloakService $keycloakService,
        UserProvisioningService $provisioningService
    ) {
        $this->keycloakService = $keycloakService;
        $this->provisioningService = $provisioningService;
    }

    /**
     * Redirect to Keycloak login page.
     *
     * @return RedirectResponse
     */
    public function redirect(): RedirectResponse
    {
        try {
            // Check if Keycloak is enabled
            if (! $this->keycloakService->isEnabled()) {
                Log::warning('Keycloak SSO is disabled');

                return redirect()
                    ->route('admin.session.create')
                    ->with('warning', trans('keycloak-sso::auth.sso_disabled'));
            }

            // Generate authorization URL with CSRF protection
            $authorizationUrl = $this->keycloakService->getAuthorizationUrl();

            Log::info('Redirecting user to Keycloak login', [
                'url' => $authorizationUrl,
            ]);

            return redirect()->away($authorizationUrl);
        } catch (KeycloakConnectionException $e) {
            ErrorHandler::handle($e, 'Failed to redirect to Keycloak - connection error', [
                'keycloak_enabled' => config('keycloak.enabled'),
            ]);

            return $this->handleAuthenticationError($e, 'connection');
        } catch (KeycloakException $e) {
            ErrorHandler::handle($e, 'Failed to redirect to Keycloak', [
                'keycloak_enabled' => config('keycloak.enabled'),
            ]);

            return $this->handleAuthenticationError($e);
        }
    }

    /**
     * Handle OAuth2 callback from Keycloak.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            // Check if Keycloak is enabled
            if (! $this->keycloakService->isEnabled()) {
                Log::warning('Keycloak SSO is disabled');

                return redirect()
                    ->route('admin.session.create')
                    ->with('warning', trans('keycloak-sso::auth.sso_disabled'));
            }

            // Handle the callback and get tokens + user info
            $callbackData = $this->keycloakService->handleCallback($request);

            $tokens = $callbackData['tokens'];
            $keycloakUser = $callbackData['user'];

            Log::info('Keycloak callback successful', [
                'keycloak_id' => $keycloakUser['sub'] ?? null,
                'email' => $keycloakUser['email'] ?? null,
            ]);

            // Provision or update user in Krayin CRM
            $user = $this->provisioningService->findOrCreateUser($keycloakUser);

            // Update user's Keycloak tokens
            $user->setKeycloakRefreshToken($tokens['refresh_token']);
            $user->updateKeycloakTokenExpiration($tokens['expires_in']);
            $user->save();

            // Log the user in
            Auth::guard('user')->login($user, true);

            // Store access token in session for API calls
            session(['keycloak_access_token' => $tokens['access_token']]);

            // Fire successful login event
            event(new KeycloakLoginSuccessful($user, $keycloakUser));

            Log::info('User logged in successfully via Keycloak', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return redirect()
                ->intended(route('admin.dashboard.index'))
                ->with('success', trans('keycloak-sso::auth.login_success'));
        } catch (KeycloakUserProvisioningException $e) {
            ErrorHandler::handle($e, 'User provisioning failed during Keycloak callback', [
                'request_params' => array_keys($request->all()),
            ]);

            // Fire failed login event
            event(new KeycloakLoginFailed($e, $request->all()));

            return $this->handleAuthenticationError($e, 'provisioning');
        } catch (KeycloakAuthenticationException $e) {
            ErrorHandler::handle($e, 'Keycloak authentication failed', [
                'request_params' => array_keys($request->all()),
            ]);

            // Fire failed login event
            event(new KeycloakLoginFailed($e, $request->all()));

            return $this->handleAuthenticationError($e, 'authentication');
        } catch (KeycloakConnectionException $e) {
            ErrorHandler::handle($e, 'Keycloak connection error during callback', [
                'request_params' => array_keys($request->all()),
            ]);

            // Fire failed login event
            event(new KeycloakLoginFailed($e, $request->all()));

            return $this->handleAuthenticationError($e, 'connection');
        } catch (KeycloakException $e) {
            ErrorHandler::handle($e, 'Keycloak callback error', [
                'request_params' => array_keys($request->all()),
            ]);

            // Fire failed login event
            event(new KeycloakLoginFailed($e, $request->all()));

            return $this->handleAuthenticationError($e);
        } catch (\Exception $e) {
            ErrorHandler::handle($e, 'Unexpected error during Keycloak callback', [
                'request_params' => array_keys($request->all()),
            ]);

            // Fire failed login event
            event(new KeycloakLoginFailed($e, $request->all()));

            return $this->handleAuthenticationError($e);
        }
    }

    /**
     * Logout from Keycloak (Single Logout).
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        try {
            $user = Auth::guard('user')->user();

            if (! $user) {
                return redirect()
                    ->route('admin.session.create')
                    ->with('info', trans('keycloak-sso::auth.already_logged_out'));
            }

            Log::info('Logging out user from Keycloak', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            // Logout from Keycloak if user is a Keycloak user
            if ($user->isKeycloakUser() && $this->keycloakService->isEnabled()) {
                $refreshToken = $user->getKeycloakRefreshToken();

                if ($refreshToken) {
                    // Revoke the refresh token on Keycloak
                    $this->keycloakService->logout($refreshToken);
                }

                // Clear Keycloak data from user
                $user->clearKeycloakData();
                $user->save();
            }

            // Clear session data
            session()->forget(['keycloak_access_token', 'keycloak_state']);

            // Logout from Laravel
            Auth::guard('user')->logout();

            // Invalidate session
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Fire logout event
            event(new KeycloakLogoutSuccessful($user));

            Log::info('User logged out successfully', [
                'user_id' => $user->id,
            ]);

            // Redirect to Keycloak logout endpoint if user was a Keycloak user
            if ($user->isKeycloakUser() && $this->keycloakService->isEnabled()) {
                $logoutUrl = $this->keycloakService->getLogoutRedirectUrl(
                    route('admin.session.create')
                );

                return redirect()->away($logoutUrl);
            }

            return redirect()
                ->route('admin.session.create')
                ->with('success', trans('keycloak-sso::auth.logout_success'));
        } catch (\Exception $e) {
            ErrorHandler::handle($e, 'Error during Keycloak logout', [
                'user_id' => $user->id ?? 'unknown',
                'is_keycloak_user' => $user?->isKeycloakUser() ?? false,
            ]);

            // Still logout locally even if Keycloak logout fails
            if (Auth::guard('user')->check()) {
                Auth::guard('user')->logout();
            }
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.session.create')
                ->with('warning', trans('keycloak-sso::auth.logout_partial_success'));
        }
    }

    /**
     * Handle authentication error with appropriate redirect.
     *
     * @param  \Exception  $exception
     * @param  string|null  $errorType
     * @return RedirectResponse
     */
    protected function handleAuthenticationError(\Exception $exception, ?string $errorType = null): RedirectResponse
    {
        // Check if fallback to local auth is enabled
        $allowLocalAuth = config('keycloak.allow_local_auth', true);
        $fallbackOnError = config('keycloak.fallback_on_error', true);

        // Get user-friendly error message
        $userMessage = ErrorHandler::getUserMessage($exception);

        // Add specific fallback message for connection errors
        if ($errorType === 'connection' && $fallbackOnError && $allowLocalAuth) {
            return redirect()
                ->route('admin.session.create')
                ->with('warning', trans('keycloak::errors.fallback.keycloak_unavailable'))
                ->with('info', $userMessage);
        }

        // Generic fallback for other errors
        if ($fallbackOnError && $allowLocalAuth) {
            return redirect()
                ->route('admin.session.create')
                ->with('error', $userMessage)
                ->with('info', trans('keycloak::errors.fallback.using_local_auth'));
        }

        // No fallback - show error
        return redirect()
            ->route('admin.session.create')
            ->with('error', $userMessage);
    }
}
