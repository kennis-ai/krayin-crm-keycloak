<?php

namespace Webkul\KeycloakSSO\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * KeycloakTokenRefresh Middleware
 *
 * Automatically refreshes expired Keycloak tokens.
 *
 * @todo Implementation in Phase 7: Middleware and Guards
 */
class KeycloakTokenRefresh
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
