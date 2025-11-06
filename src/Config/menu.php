<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Keycloak SSO Admin Menu Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for admin menu items for Keycloak SSO management.
    | This file should be published or manually added to Krayin's menu config.
    |
    */

    [
        'key'        => 'keycloak',
        'name'       => 'keycloak::admin.config.title',
        'route'      => 'admin.keycloak.config.index',
        'sort'       => 6,
        'icon-class' => 'icon key-icon',
    ],

    [
        'key'        => 'keycloak.config',
        'name'       => 'keycloak::admin.config.general_settings',
        'route'      => 'admin.keycloak.config.edit',
        'sort'       => 1,
        'icon-class' => 'icon settings-icon',
    ],

    [
        'key'        => 'keycloak.role-mappings',
        'name'       => 'keycloak::admin.config.role_mappings_title',
        'route'      => 'admin.keycloak.config.role-mappings',
        'sort'       => 2,
        'icon-class' => 'icon users-icon',
    ],

    [
        'key'        => 'keycloak.users',
        'name'       => 'keycloak::admin.config.users_title',
        'route'      => 'admin.keycloak.config.users',
        'sort'       => 3,
        'icon-class' => 'icon list-icon',
    ],
];
