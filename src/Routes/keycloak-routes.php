<?php

use Illuminate\Support\Facades\Route;
use Webkul\KeycloakSSO\Http\Controllers\KeycloakAuthController;

/**
 * Keycloak SSO Authentication Routes
 *
 * These routes handle the OAuth2/OpenID Connect authentication flow with Keycloak.
 * Routes are only registered if Keycloak SSO is enabled in configuration.
 */

// Only register routes if Keycloak is enabled
if (! config('keycloak.enabled')) {
    return;
}

Route::prefix('admin/auth/keycloak')->name('admin.keycloak.')->group(function () {
    /**
     * Redirect to Keycloak Login
     *
     * Initiates the OAuth2 authorization flow by redirecting the user
     * to the Keycloak login page.
     */
    Route::get('login', [KeycloakAuthController::class, 'redirect'])
        ->name('login')
        ->middleware('web');

    /**
     * Handle OAuth2 Callback
     *
     * Processes the callback from Keycloak after successful authentication.
     * Exchanges the authorization code for access tokens and logs the user in.
     */
    Route::get('callback', [KeycloakAuthController::class, 'callback'])
        ->name('callback')
        ->middleware('web');

    /**
     * Logout from Keycloak
     *
     * Logs the user out from both the application and Keycloak (Single Logout).
     * Requires the user to be authenticated.
     */
    Route::post('logout', [KeycloakAuthController::class, 'logout'])
        ->name('logout')
        ->middleware(['web', 'auth:user']);
});

/**
 * Alternative public routes for environments where admin prefix is not needed
 * Uncomment if you need non-admin routes for Keycloak authentication
 */
/*
Route::prefix('auth/keycloak')->name('keycloak.')->middleware('web')->group(function () {
    Route::get('login', [KeycloakAuthController::class, 'redirect'])->name('login');
    Route::get('callback', [KeycloakAuthController::class, 'callback'])->name('callback');
    Route::post('logout', [KeycloakAuthController::class, 'logout'])->name('logout')->middleware('auth:user');
});
*/
