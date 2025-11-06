<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Keycloak SSO Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable Keycloak SSO authentication. When disabled, only
    | local authentication will be available.
    |
    */
    'enabled' => env('KEYCLOAK_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Keycloak Client Configuration
    |--------------------------------------------------------------------------
    |
    | These are the OAuth2/OpenID Connect client credentials from your
    | Keycloak realm. You must configure a client in Keycloak with
    | 'Access Type' set to 'confidential' and 'Standard Flow' enabled.
    |
    */
    'client_id' => env('KEYCLOAK_CLIENT_ID'),
    'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Keycloak Server Configuration
    |--------------------------------------------------------------------------
    |
    | The base URL of your Keycloak server and the realm to use for
    | authentication. The base URL should not include the realm path.
    |
    */
    'base_url' => env('KEYCLOAK_BASE_URL'),
    'realm' => env('KEYCLOAK_REALM', 'master'),

    /*
    |--------------------------------------------------------------------------
    | Redirect URI
    |--------------------------------------------------------------------------
    |
    | The callback URL that Keycloak will redirect to after authentication.
    | This must match the Valid Redirect URIs configured in your Keycloak client.
    |
    */
    'redirect_uri' => env('KEYCLOAK_REDIRECT_URI', env('APP_URL').'/admin/auth/keycloak/callback'),

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Control various features of the Keycloak integration.
    |
    */

    // Automatically create user accounts when they login via Keycloak
    'auto_provision_users' => env('KEYCLOAK_AUTO_PROVISION', true),

    // Sync user data from Keycloak on every login
    'sync_user_data' => env('KEYCLOAK_SYNC_USER_DATA', true),

    // Enable role mapping from Keycloak to Krayin
    'enable_role_mapping' => env('KEYCLOAK_ROLE_MAPPING', true),

    /*
    |--------------------------------------------------------------------------
    | Fallback Options
    |--------------------------------------------------------------------------
    |
    | Configure fallback behavior when Keycloak is unavailable or errors occur.
    |
    */

    // Allow users to login with local credentials when Keycloak is enabled
    'allow_local_auth' => env('KEYCLOAK_ALLOW_LOCAL_AUTH', true),

    // Fallback to local auth if Keycloak connection fails
    'fallback_on_error' => env('KEYCLOAK_FALLBACK_ON_ERROR', true),

    /*
    |--------------------------------------------------------------------------
    | Role Mapping
    |--------------------------------------------------------------------------
    |
    | Map Keycloak roles to Krayin CRM roles. The key is the Keycloak role
    | name and the value is the corresponding Krayin role name.
    |
    */
    'role_mapping' => [
        'keycloak_admin' => 'Administrator',
        'keycloak_manager' => 'Manager',
        'keycloak_user' => 'Sales Agent',
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Configuration
    |--------------------------------------------------------------------------
    |
    | Configure token handling and caching behavior.
    |
    */

    // Cache access tokens until they expire
    'cache_tokens' => env('KEYCLOAK_CACHE_TOKENS', true),

    // Token cache TTL in seconds (default: 1 hour)
    'cache_ttl' => env('KEYCLOAK_CACHE_TTL', 3600),

    // Grace period before token expiration to trigger refresh (in seconds)
    'token_refresh_grace_period' => env('KEYCLOAK_TOKEN_REFRESH_GRACE', 300),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    |
    | Configure timeouts and retry behavior for Keycloak API calls.
    |
    */

    'timeout' => [
        'connect' => env('KEYCLOAK_TIMEOUT_CONNECT', 10),
        'request' => env('KEYCLOAK_TIMEOUT_REQUEST', 30),
    ],

    'retry' => [
        'enabled' => env('KEYCLOAK_RETRY_ENABLED', true),
        'times' => env('KEYCLOAK_RETRY_TIMES', 3),
        'sleep' => env('KEYCLOAK_RETRY_SLEEP', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure logging behavior for Keycloak operations.
    |
    */

    'logging' => [
        'enabled' => env('KEYCLOAK_LOGGING_ENABLED', true),
        'channel' => env('KEYCLOAK_LOG_CHANNEL', 'stack'),
        'level' => env('KEYCLOAK_LOG_LEVEL', 'info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | Enable debug mode for additional logging and error details.
    | WARNING: Do not enable in production as it may expose sensitive data.
    |
    */
    'debug' => env('KEYCLOAK_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Error Handling Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how errors are handled and displayed to users.
    |
    */

    'error_handling' => [
        // Show detailed error messages to users (not recommended for production)
        'show_details' => env('KEYCLOAK_SHOW_ERROR_DETAILS', false),

        // Log full stack traces for errors
        'log_stack_traces' => env('KEYCLOAK_LOG_STACK_TRACES', true),

        // Maximum number of retry attempts before failing
        'max_retries' => env('KEYCLOAK_MAX_RETRIES', 3),

        // Delay between retry attempts in milliseconds
        'retry_delay' => env('KEYCLOAK_RETRY_DELAY', 1000),

        // Enable exponential backoff for retries
        'exponential_backoff' => env('KEYCLOAK_EXPONENTIAL_BACKOFF', true),
    ],
];
