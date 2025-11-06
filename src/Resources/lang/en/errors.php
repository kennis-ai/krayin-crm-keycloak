<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Error Messages - English
    |--------------------------------------------------------------------------
    |
    | User-friendly error messages for Keycloak SSO integration.
    |
    */

    // Connection Errors
    'connection' => [
        'title' => 'Connection Error',
        'failed' => 'Unable to connect to authentication server. Please try again later.',
        'timeout' => 'Connection to authentication server timed out. Please check your internet connection and try again.',
        'unreachable' => 'Authentication server is currently unreachable. Please contact your administrator if the problem persists.',
        'ssl_error' => 'Secure connection could not be established. Please contact your administrator.',
    ],

    // Authentication Errors
    'authentication' => [
        'title' => 'Authentication Error',
        'failed' => 'Authentication failed. Please check your credentials and try again.',
        'invalid_credentials' => 'Invalid credentials provided. Please try again.',
        'access_denied' => 'Access denied. You do not have permission to access this application.',
        'cancelled' => 'Authentication was cancelled. You can try again when ready.',
        'invalid_state' => 'Invalid authentication state. This may be due to an expired session. Please try again.',
    ],

    // Token Errors
    'token' => [
        'title' => 'Token Error',
        'expired' => 'Your session has expired. Please log in again.',
        'invalid' => 'Invalid authentication token. Please log in again.',
        'refresh_failed' => 'Failed to refresh your session. Please log in again.',
        'revocation_failed' => 'Failed to revoke your session. You may need to clear your browser cookies.',
    ],

    // User Provisioning Errors
    'provisioning' => [
        'title' => 'Account Setup Error',
        'failed' => 'Failed to set up your account. Please contact your administrator.',
        'creation_failed' => 'Could not create your user account. Please contact your administrator.',
        'update_failed' => 'Failed to update your account information. Please try again later.',
        'missing_email' => 'Email address is required but was not provided by the authentication server.',
        'missing_name' => 'Name information is missing. Please ensure your profile is complete.',
        'duplicate_user' => 'An account with this email already exists with a different authentication method.',
        'role_mapping_failed' => 'Failed to assign proper permissions to your account. Please contact your administrator.',
    ],

    // Configuration Errors
    'configuration' => [
        'title' => 'Configuration Error',
        'invalid' => 'Authentication system is not properly configured. Please contact your administrator.',
        'missing_required' => 'Required configuration is missing. Please contact your administrator.',
        'disabled' => 'Single Sign-On authentication is currently disabled.',
    ],

    // Generic Errors
    'generic' => [
        'title' => 'An Error Occurred',
        'unknown' => 'An unexpected error occurred. Please try again or contact your administrator if the problem persists.',
        'server_error' => 'A server error occurred. Please try again later.',
        'maintenance' => 'The authentication system is currently under maintenance. Please try again later.',
    ],

    // Fallback Messages
    'fallback' => [
        'using_local_auth' => 'Single Sign-On is unavailable. Using local authentication instead.',
        'keycloak_unavailable' => 'Single Sign-On service is temporarily unavailable. You can still log in with your local credentials.',
    ],

    // Debug Messages (only shown when debug mode is enabled)
    'debug' => [
        'exception_details' => 'Exception: :exception',
        'error_code' => 'Error Code: :code',
        'file_line' => 'File: :file at line :line',
        'stack_trace' => 'Stack Trace',
    ],
];
