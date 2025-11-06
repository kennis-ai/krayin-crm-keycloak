<?php

namespace Webkul\KeycloakSSO\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * KeycloakAuthController
 *
 * Handles Keycloak OAuth2/OpenID Connect authentication flow.
 *
 * @todo Implementation in Phase 5: Authentication Controller
 */
class KeycloakAuthController extends Controller
{
    /**
     * Redirect to Keycloak login page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        // Implementation in Phase 5
        return redirect()->back()->with('error', 'Keycloak authentication not yet implemented');
    }

    /**
     * Handle OAuth2 callback from Keycloak.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
    {
        // Implementation in Phase 5
        return redirect()->route('admin.dashboard')->with('error', 'Keycloak callback not yet implemented');
    }

    /**
     * Logout from Keycloak (Single Logout).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // Implementation in Phase 5
        return redirect()->route('admin.session.create')->with('success', 'Logged out successfully');
    }
}
