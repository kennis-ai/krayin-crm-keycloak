# Keycloak SSO View Components

This directory contains Blade view components for integrating Keycloak SSO into your Krayin CRM installation.

## Available Components

### Login Button Component

**File:** `login-button.blade.php`

A ready-to-use login button component that can be added to any login page to enable Keycloak SSO authentication.

## Integration Guide

### Quick Start

To add Keycloak SSO login to your Krayin admin login page:

1. Open your Krayin admin login view (typically located at `packages/Webkul/Admin/src/Resources/views/sessions/create.blade.php`)

2. Add the login button component inside your login form:

```blade
@include('keycloak-sso::login-button')
```

### Complete Integration Example

Here's a complete example of integrating the Keycloak login button into a Krayin login page:

```blade
<div class="login-container">
    <form method="POST" action="{{ route('admin.session.store') }}">
        @csrf

        <!-- Email Input -->
        <div class="form-group">
            <label for="email">{{ __('admin::app.users.sessions.email') }}</label>
            <input type="email" name="email" id="email" required>
        </div>

        <!-- Password Input -->
        <div class="form-group">
            <label for="password">{{ __('admin::app.users.sessions.password') }}</label>
            <input type="password" name="password" id="password" required>
        </div>

        <!-- Submit Button -->
        <button type="submit">{{ __('admin::app.users.sessions.submit-btn') }}</button>
    </form>

    <!-- Keycloak SSO Login Button -->
    @include('keycloak-sso::login-button')
</div>
```

### Customization Options

The login button component accepts optional parameters for customization:

```blade
@include('keycloak-sso::login-button', [
    'showDivider' => true,           // Show/hide divider line (default: true)
    'buttonClass' => 'my-custom-class', // Additional CSS classes for button
    'containerClass' => 'my-container'  // Additional CSS classes for container
])
```

### Styling

The component includes default styling that matches common design patterns. The styles include:

- Responsive button design
- Hover and focus states
- Dark mode support
- Keycloak icon
- Divider with "Or continue with" text

You can override these styles by adding your own CSS after the component inclusion:

```blade
@include('keycloak-sso::login-button')

<style>
    .btn-keycloak {
        background-color: #your-color;
        /* Your custom styles */
    }
</style>
```

### Alternative: Custom Implementation

If you prefer to create your own button design, you can use the route directly:

```blade
<a href="{{ route('admin.keycloak.login') }}" class="your-custom-class">
    Login with Keycloak
</a>
```

## Routes Available

The following routes are registered when Keycloak SSO is enabled:

- `admin.keycloak.login` - Initiates Keycloak login flow
- `admin.keycloak.callback` - Handles OAuth callback (automatic)
- `admin.keycloak.logout` - Logs out from Keycloak (POST)

## Configuration

Ensure Keycloak SSO is enabled in your configuration:

```php
// config/keycloak.php
'enabled' => env('KEYCLOAK_ENABLED', true),
```

The login button will only be displayed if Keycloak is enabled in the configuration.

## Conditional Display

If you want to show the button only under certain conditions:

```blade
@if(config('keycloak.enabled') && $someCondition)
    @include('keycloak-sso::login-button')
@endif
```

## Multiple Login Options

If you have multiple SSO providers, you can include multiple buttons:

```blade
<!-- Local Login Form -->
<form>...</form>

<!-- Divider -->
<div class="or-divider">
    <span>{{ __('Or continue with') }}</span>
</div>

<!-- Keycloak SSO -->
@include('keycloak-sso::login-button', ['showDivider' => false])

<!-- Other SSO Providers -->
<!-- ... -->
```

## Troubleshooting

### Button Not Appearing

1. Check if Keycloak is enabled: `config('keycloak.enabled')`
2. Verify view is published: `php artisan vendor:publish --tag=keycloak-views`
3. Clear view cache: `php artisan view:clear`

### Styling Issues

1. Check for CSS conflicts with your theme
2. Use browser developer tools to inspect CSS
3. Add custom CSS overrides as needed

### Route Errors

1. Clear route cache: `php artisan route:clear`
2. Verify routes are registered: `php artisan route:list | grep keycloak`
3. Check Keycloak configuration is correct

## Support

For more information, refer to the main package documentation or visit the GitHub repository.
