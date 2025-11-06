# Menu Integration Guide

This guide explains how to integrate the Keycloak SSO admin menu into Krayin CRM.

## Option 1: Manual Integration (Recommended)

Add the following menu configuration to your Krayin CRM's menu configuration file (typically `config/menu.php` or similar):

```php
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
```

## Option 2: Programmatic Integration

If Krayin CRM supports programmatic menu registration, use the `src/Config/menu.php` file provided in this package.

## Option 3: Direct Link Access

Even without menu integration, you can access the Keycloak SSO configuration pages directly via these URLs:

- Dashboard: `/admin/keycloak/config`
- Settings: `/admin/keycloak/config/edit`
- Role Mappings: `/admin/keycloak/config/role-mappings`
- Users: `/admin/keycloak/config/users`

## Verification

After integration, verify the menu appears in your admin panel by:

1. Logging into the admin panel
2. Looking for "Keycloak SSO Configuration" or "Keycloak" in the navigation menu
3. Clicking the menu item should take you to the configuration dashboard

## Troubleshooting

### Menu Not Appearing

1. Clear cache: `php artisan config:clear`
2. Verify the routes are loaded: `php artisan route:list | grep keycloak`
3. Check translations are published: `php artisan vendor:publish --tag=keycloak-lang`

### Permission Issues

Ensure the admin middleware is properly configured and the logged-in user has admin permissions.
