<?php

namespace Webkul\KeycloakSSO\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * KeycloakAuthenticate Middleware
 *
 * Verifies Keycloak authentication and redirects to login if needed.
 *
 * @todo Implementation in Phase 7: Middleware and Guards
 */
class KeycloakAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Implementation in Phase 7
        return $next($request);
    }
}
