# Installation Guide

Complete guide for installing the Keycloak SSO Extension for Krayin CRM.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Installation Methods](#installation-methods)
- [Keycloak Server Setup](#keycloak-server-setup)
- [Post-Installation](#post-installation)
- [Verification](#verification)
- [Troubleshooting Installation](#troubleshooting-installation)

## Prerequisites

### System Requirements

- **PHP**: >= 8.2
- **Laravel**: >= 10.0
- **Krayin CRM**: >= 2.0
- **Composer**: >= 2.5
- **Keycloak Server**: >= 20.0

### PHP Extensions Required

```bash
# Check installed extensions
php -m

# Required extensions
- OpenSSL
- PDO
- Mbstring
- Tokenizer
- XML
- Ctype
- JSON
- BCMath
```

### Server Requirements

- HTTPS enabled (required for production)
- Accessible Keycloak server
- Database: MySQL 5.7+ or PostgreSQL 10+

## Installation Methods

### Method 1: Composer Installation (Recommended)

#### Step 1: Install Package

```bash
cd /path/to/krayin-crm
composer require webkul/laravel-keycloak-sso
```

#### Step 2: Publish Configuration and Assets

```bash
# Publish all package assets
php artisan vendor:publish --provider="Webkul\KeycloakSSO\Providers\KeycloakSSOServiceProvider"

# Or publish selectively
php artisan vendor:publish --tag=keycloak-config
php artisan vendor:publish --tag=keycloak-migrations
php artisan vendor:publish --tag=keycloak-lang
php artisan vendor:publish --tag=keycloak-views
```

#### Step 3: Run Migrations

```bash
php artisan migrate
```

This will add the following columns to the `users` table:
- `keycloak_id` - Keycloak user identifier
- `auth_provider` - Authentication provider (keycloak/local)
- `keycloak_refresh_token` - Encrypted refresh token
- `keycloak_token_expires_at` - Token expiration timestamp

#### Step 4: Configure Environment Variables

Add to your `.env` file:

```env
# Enable Keycloak SSO
KEYCLOAK_ENABLED=true

# Keycloak Server Configuration
KEYCLOAK_CLIENT_ID=your-client-id
KEYCLOAK_CLIENT_SECRET=your-client-secret
KEYCLOAK_BASE_URL=https://keycloak.example.com
KEYCLOAK_REALM=master
KEYCLOAK_REDIRECT_URI=https://crm.example.com/admin/auth/keycloak/callback

# Feature Flags
KEYCLOAK_AUTO_PROVISION=true
KEYCLOAK_SYNC_USER_DATA=true
KEYCLOAK_ENABLE_ROLE_MAPPING=true

# Fallback Options
KEYCLOAK_ALLOW_LOCAL_AUTH=true
KEYCLOAK_FALLBACK_ON_ERROR=true

# HTTP Configuration
KEYCLOAK_HTTP_TIMEOUT=30

# Cache Configuration (optional)
KEYCLOAK_CACHE_USER_INFO=true
KEYCLOAK_CACHE_TTL=300

# Error Handling (optional)
KEYCLOAK_SHOW_ERROR_DETAILS=false
KEYCLOAK_LOG_STACK_TRACES=true
KEYCLOAK_MAX_RETRIES=3
KEYCLOAK_RETRY_DELAY=1000
KEYCLOAK_EXPONENTIAL_BACKOFF=true
```

#### Step 5: Clear Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Method 2: Manual Installation

#### Step 1: Clone Repository

```bash
cd packages/Webkul
git clone https://github.com/kennis-ai/krayin-crm-keycloak.git KeycloakSSO
```

#### Step 2: Update composer.json

Add to your root `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "Webkul\\KeycloakSSO\\": "packages/Webkul/KeycloakSSO/src/"
        }
    }
}
```

#### Step 3: Register Service Provider

Add to `config/app.php`:

```php
'providers' => [
    // ... other providers
    Webkul\KeycloakSSO\Providers\KeycloakSSOServiceProvider::class,
],
```

#### Step 4: Run Composer

```bash
composer dump-autoload
```

#### Step 5: Publish and Migrate

```bash
php artisan vendor:publish --provider="Webkul\KeycloakSSO\Providers\KeycloakSSOServiceProvider"
php artisan migrate
```

## Keycloak Server Setup

### Step 1: Create Realm (Optional)

1. Log in to Keycloak Admin Console
2. Click **Add realm** (or use existing realm)
3. Enter realm name (e.g., `krayin-crm`)
4. Click **Create**

### Step 2: Create Client

1. Navigate to **Clients** → **Create**
2. **Client ID**: `krayin-crm` (or your preferred ID)
3. **Client Protocol**: `openid-connect`
4. Click **Save**

### Step 3: Configure Client Settings

**Settings Tab:**
- **Access Type**: `confidential`
- **Standard Flow Enabled**: `ON`
- **Direct Access Grants Enabled**: `ON` (optional, for testing)
- **Valid Redirect URIs**:
  - `https://your-crm-domain.com/admin/auth/keycloak/callback`
  - `http://localhost:8000/admin/auth/keycloak/callback` (for dev)
- **Web Origins**: `https://your-crm-domain.com`
- Click **Save**

**Credentials Tab:**
- Copy the **Client Secret**
- Add to your `.env` as `KEYCLOAK_CLIENT_SECRET`

### Step 4: Configure Roles

1. Navigate to **Roles** → **Add Role**
2. Create roles matching your Krayin CRM roles:
   - `admin` - For administrators
   - `manager` - For managers
   - `sales` - For sales agents

### Step 5: Create Test User

1. Navigate to **Users** → **Add user**
2. Fill in user details
3. Click **Save**
4. Go to **Credentials** tab
5. Set password and disable **Temporary**
6. Go to **Role Mappings** tab
7. Assign appropriate roles

## Post-Installation

### Step 1: Configure Role Mapping

Edit `config/keycloak.php`:

```php
'role_mapping' => [
    'admin' => 'Administrator',
    'manager' => 'Manager',
    'sales' => 'Sales Agent',
],

'default_role' => 'Sales Agent',
```

### Step 2: Test Connection

#### Using Admin UI (if Phase 11 is complete):

1. Navigate to `/admin/keycloak/config`
2. Click **Test Connection**
3. Verify connection status

#### Using Command Line:

```bash
php artisan tinker

# Test Keycloak configuration
$service = app(\Webkul\KeycloakSSO\Services\KeycloakService::class);
$authUrl = $service->getAuthorizationUrl();
echo $authUrl;
```

### Step 3: Integrate Login Button

#### Option 1: Automatic Integration (if supported)

The package may automatically add the login button to your login page.

#### Option 2: Manual Integration

Add to your login view (e.g., `packages/Webkul/Admin/src/Resources/views/users/sessions/create.blade.php`):

```blade
@if(config('keycloak.enabled'))
    @include('keycloak::login-button')
@endif
```

Or use the component directly:

```blade
<x-keycloak-login-button
    text="Login with Keycloak"
    class="custom-class"
/>
```

### Step 4: Configure Permissions (Optional)

If using Krayin's ACL system, ensure Keycloak routes are accessible:

```php
// config/acl.php
[
    'key' => 'keycloak',
    'name' => 'Keycloak SSO',
    'route' => 'keycloak.*',
    'sort' => 10,
]
```

## Verification

### Step 1: Verify Installation

```bash
# Check if migrations ran
php artisan migrate:status | grep keycloak

# Check if configuration is published
ls -la config/keycloak.php

# Check if routes are registered
php artisan route:list | grep keycloak
```

Expected routes:
```
GET|HEAD   admin/auth/keycloak/login ......... keycloak.login
GET|HEAD   admin/auth/keycloak/callback ...... keycloak.callback
POST       admin/auth/keycloak/logout ........ keycloak.logout
```

### Step 2: Test Authentication Flow

1. Navigate to your Krayin CRM login page
2. Click "Login with Keycloak" button
3. You should be redirected to Keycloak
4. Log in with test user credentials
5. You should be redirected back to CRM dashboard
6. Verify user was created/updated in database

```sql
-- Check if user was provisioned
SELECT id, name, email, keycloak_id, auth_provider
FROM users
WHERE auth_provider = 'keycloak';
```

### Step 3: Test Token Refresh

1. Log in via Keycloak
2. Wait for token to expire (default: ~5 minutes)
3. Navigate between pages
4. Token should refresh automatically
5. Check logs for token refresh events:

```bash
tail -f storage/logs/laravel.log | grep "Keycloak token refreshed"
```

### Step 4: Test Logout

1. Click logout button
2. You should be logged out from both CRM and Keycloak
3. Verify session is cleared

## Troubleshooting Installation

### Issue: Composer Install Fails

**Error**: `Package webkul/laravel-keycloak-sso could not be found`

**Solution**:
- Verify package name is correct
- Check if you have access to the repository
- Try: `composer clearcache && composer install`

### Issue: Migrations Fail

**Error**: `Table 'users' already exists`

**Solution**:
```bash
# Check migration status
php artisan migrate:status

# If needed, refresh migrations (CAUTION: this will drop tables)
php artisan migrate:fresh

# Or manually run only Keycloak migrations
php artisan migrate --path=vendor/webkul/laravel-keycloak-sso/src/Database/Migrations
```

### Issue: Configuration Not Publishing

**Error**: `config/keycloak.php not found`

**Solution**:
```bash
# Force publish
php artisan vendor:publish --provider="Webkul\KeycloakSSO\Providers\KeycloakSSOServiceProvider" --force

# Check published files
ls -la config/keycloak.php
```

### Issue: Routes Not Working

**Error**: `Route [keycloak.login] not defined`

**Solution**:
```bash
# Clear route cache
php artisan route:clear
php artisan config:clear

# Verify routes are loaded
php artisan route:list | grep keycloak

# Check if service provider is registered
php artisan tinker
>>> app()->getProviders('Webkul\KeycloakSSO\Providers\KeycloakSSOServiceProvider')
```

### Issue: Database Connection Error

**Error**: `SQLSTATE[HY000] [2002] Connection refused`

**Solution**:
- Verify database credentials in `.env`
- Ensure database server is running
- Test connection: `php artisan db:show`

### Issue: Permission Denied on Cache

**Error**: `file_put_contents(/path/to/cache): failed to open stream`

**Solution**:
```bash
# Fix permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Or for development
chmod -R 777 storage bootstrap/cache
```

## Next Steps

After successful installation:

1. **Configure Settings**: See [CONFIGURATION.md](CONFIGURATION.md)
2. **Test Integration**: Follow [Testing Guide](tests/README.md)
3. **Read Documentation**: Check the [Wiki](https://github.com/kennis-ai/krayin-crm-keycloak/wiki)
4. **Report Issues**: Use the [Issue Tracker](https://github.com/kennis-ai/krayin-crm-keycloak/issues)

## Support

For installation help:
- 📖 [Documentation](https://github.com/kennis-ai/krayin-crm-keycloak/wiki)
- 🐛 [Issue Tracker](https://github.com/kennis-ai/krayin-crm-keycloak/issues)
- 📧 Email: suporte@kennis.com.br
