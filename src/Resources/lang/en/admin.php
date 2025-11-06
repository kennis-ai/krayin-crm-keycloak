<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin UI Language Lines - English
    |--------------------------------------------------------------------------
    |
    | Translation strings for Keycloak SSO admin interface.
    |
    */

    'config' => [
        // Page Titles
        'title' => 'Keycloak SSO Configuration',
        'edit_title' => 'Edit Keycloak Configuration',
        'role_mappings_title' => 'Role Mappings',
        'users_title' => 'Keycloak Users',

        // Buttons
        'configure' => 'Configure',
        'save' => 'Save Settings',
        'cancel' => 'Cancel',
        'back' => 'Back',
        'test_connection' => 'Test Connection',
        'save_mappings' => 'Save Mappings',
        'add_mapping' => 'Add Mapping',
        'sync_user' => 'Sync User',

        // Status
        'status' => 'Status',
        'enabled' => 'Enabled',
        'disabled' => 'Disabled',

        // Statistics
        'total_users' => 'Total Users',
        'keycloak_users' => 'Keycloak Users',
        'local_users' => 'Local Users',
        'total_keycloak_users' => 'Total Keycloak Users',
        'active_this_week' => 'Active This Week',
        'active_today' => 'Active Today',

        // Configuration Sections
        'connection_info' => 'Connection Information',
        'features' => 'Features',
        'quick_actions' => 'Quick Actions',
        'recent_users' => 'Recent Keycloak Users',
        'general_settings' => 'General Settings',
        'connection_settings' => 'Connection Settings',
        'feature_settings' => 'Feature Settings',
        'statistics' => 'Statistics',

        // Fields
        'base_url' => 'Keycloak Server URL',
        'realm' => 'Realm',
        'client_id' => 'Client ID',
        'client_secret' => 'Client Secret',
        'redirect_uri' => 'Redirect URI',
        'auto_provision' => 'Auto Provision Users',
        'sync_user_data' => 'Sync User Data',
        'role_mapping' => 'Role Mapping',
        'fallback_local' => 'Fallback to Local Auth',

        // Help Text
        'base_url_help' => 'The base URL of your Keycloak server (e.g., https://keycloak.example.com)',
        'realm_help' => 'The Keycloak realm name for authentication',
        'client_id_help' => 'The OAuth2 client ID from your Keycloak client configuration',
        'client_secret_help' => 'The OAuth2 client secret from your Keycloak client configuration',
        'redirect_uri_help' => 'The callback URL that Keycloak will redirect to after authentication',
        'enable_sso' => 'Enable Keycloak SSO',
        'enable_sso_help' => 'Enable Single Sign-On authentication via Keycloak',
        'auto_provision_users' => 'Auto Provision Users',
        'auto_provision_users_help' => 'Automatically create user accounts when they login via Keycloak',
        'sync_user_data' => 'Sync User Data',
        'sync_user_data_help' => 'Synchronize user information from Keycloak on every login',
        'enable_role_mapping' => 'Enable Role Mapping',
        'enable_role_mapping_help' => 'Map Keycloak roles to Krayin CRM roles automatically',
        'allow_local_auth' => 'Allow Local Authentication',
        'allow_local_auth_help' => 'Allow users to login with local credentials when Keycloak is enabled',
        'fallback_on_error' => 'Fallback on Error',
        'fallback_on_error_help' => 'Automatically fallback to local authentication if Keycloak connection fails',

        // Actions
        'manage_role_mappings' => 'Manage Role Mappings',
        'view_keycloak_users' => 'View Keycloak Users',

        // Role Mappings
        'role_mappings_info' => 'Role Mapping Configuration',
        'role_mappings_description' => 'Map Keycloak roles to Krayin CRM roles. Users will be assigned Krayin roles based on their Keycloak roles.',
        'keycloak_role' => 'Keycloak Role',
        'krayin_role' => 'Krayin Role',
        'select_role' => 'Select Role',
        'no_mappings' => 'No role mappings configured. Click "Add Mapping" to create one.',
        'available_roles' => 'Available Krayin Roles',
        'role_name' => 'Role Name',
        'users_count' => 'Users Count',

        // Users List
        'keycloak_users_list' => 'Keycloak Users List',
        'id' => 'ID',
        'name' => 'Name',
        'email' => 'Email',
        'role' => 'Role',
        'keycloak_id' => 'Keycloak ID',
        'last_updated' => 'Last Updated',
        'actions' => 'Actions',
        'no_keycloak_users' => 'No users have logged in via Keycloak yet.',

        // Messages
        'update_success' => 'Keycloak configuration updated successfully.',
        'update_failed' => 'Failed to update Keycloak configuration. Please check your settings and try again.',
        'test_success' => 'Connection to Keycloak server successful!',
        'test_failed' => 'Failed to connect to Keycloak server. Please check your configuration.',
        'test_error' => 'An error occurred while testing the connection.',
        'test_disabled' => 'Keycloak SSO is currently disabled.',
        'role_mappings_updated' => 'Role mappings updated successfully.',
        'role_mappings_failed' => 'Failed to update role mappings.',
        'user_not_keycloak' => 'This user is not a Keycloak user.',
        'sync_not_implemented' => 'Manual user sync is not yet implemented.',
        'sync_failed' => 'Failed to sync user data.',
    ],
];
