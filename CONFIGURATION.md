# Configuration Guide

Complete configuration reference for Keycloak SSO Extension for Krayin CRM.

## Table of Contents

- [Configuration File](#configuration-file)
- [Environment Variables](#environment-variables)
- [Basic Configuration](#basic-configuration)
- [Advanced Configuration](#advanced-configuration)
- [Role Mapping](#role-mapping)
- [Security Settings](#security-settings)
- [Error Handling](#error-handling)
- [Performance Tuning](#performance-tuning)
- [Development vs Production](#development-vs-production)

## Configuration File

The main configuration file is located at `config/keycloak.php`. This file is published during installation:

```bash
php artisan vendor:publish --tag=keycloak-config
```

## Environment Variables

### Required Variables

```env
# Enable/Disable Keycloak SSO
KEYCLOAK_ENABLED=true

# Keycloak Server URL (without /auth)
KEYCLOAK_BASE_URL=https://keycloak.example.com

# Keycloak Realm
KEYCLOAK_REALM=master

# OAuth2 Client Credentials
KEYCLOAK_CLIENT_ID=krayin-crm
KEYCLOAK_CLIENT_SECRET=your-client-secret-here

# Redirect URI (must match Keycloak client configuration)
KEYCLOAK_REDIRECT_URI=https://crm.example.com/admin/auth/keycloak/callback
```

### Optional Variables

```env
# Feature Flags
KEYCLOAK_AUTO_PROVISION=true
KEYCLOAK_SYNC_USER_DATA=true
KEYCLOAK_ENABLE_ROLE_MAPPING=true

# Fallback Options
KEYCLOAK_ALLOW_LOCAL_AUTH=true
KEYCLOAK_FALLBACK_ON_ERROR=true

# HTTP Configuration
KEYCLOAK_HTTP_TIMEOUT=30

# Cache Configuration
KEYCLOAK_CACHE_USER_INFO=true
KEYCLOAK_CACHE_TTL=300

# Error Handling
KEYCLOAK_SHOW_ERROR_DETAILS=false
KEYCLOAK_LOG_STACK_TRACES=true
KEYCLOAK_MAX_RETRIES=3
KEYCLOAK_RETRY_DELAY=1000
KEYCLOAK_EXPONENTIAL_BACKOFF=true
```

## Basic Configuration

### Enabling/Disabling Keycloak SSO

```env
# Enable Keycloak SSO
KEYCLOAK_ENABLED=true

# Disable Keycloak SSO (users can only use local auth)
KEYCLOAK_ENABLED=false
```

### Keycloak Server Connection

#### Single Realm Configuration

```env
KEYCLOAK_BASE_URL=https://keycloak.example.com
KEYCLOAK_REALM=production
KEYCLOAK_CLIENT_ID=krayin-prod
KEYCLOAK_CLIENT_SECRET=secret-key-here
```

#### Development vs Production

**.env.development:**
```env
KEYCLOAK_BASE_URL=http://localhost:8080
KEYCLOAK_REALM=development
KEYCLOAK_CLIENT_ID=krayin-dev
KEYCLOAK_CLIENT_SECRET=dev-secret
KEYCLOAK_REDIRECT_URI=http://localhost:8000/admin/auth/keycloak/callback
```

**.env.production:**
```env
KEYCLOAK_BASE_URL=https://keycloak.company.com
KEYCLOAK_REALM=production
KEYCLOAK_CLIENT_ID=krayin-prod
KEYCLOAK_CLIENT_SECRET=${KEYCLOAK_PROD_SECRET}
KEYCLOAK_REDIRECT_URI=https://crm.company.com/admin/auth/keycloak/callback
```

### Redirect URI Configuration

The redirect URI must:
1. Match exactly in Keycloak client configuration
2. Use HTTPS in production
3. Be accessible from user browsers

```env
# Production
KEYCLOAK_REDIRECT_URI=https://crm.example.com/admin/auth/keycloak/callback

# Development
KEYCLOAK_REDIRECT_URI=http://localhost:8000/admin/auth/keycloak/callback

# Multiple environments (use APP_URL)
KEYCLOAK_REDIRECT_URI=${APP_URL}/admin/auth/keycloak/callback
```

## Advanced Configuration

### User Provisioning

#### Auto-Provision New Users

```env
# Automatically create users on first Keycloak login
KEYCLOAK_AUTO_PROVISION=true

# Disable auto-provisioning (users must exist in CRM first)
KEYCLOAK_AUTO_PROVISION=false
```

When `AUTO_PROVISION=true`:
- New users are created automatically
- User data is extracted from Keycloak claims
- Roles are mapped and assigned
- User is activated immediately

When `AUTO_PROVISION=false`:
- Only existing users can log in via Keycloak
- User account must be created manually first
- Accounts are linked by email address

#### User Data Synchronization

```env
# Sync user data from Keycloak on every login
KEYCLOAK_SYNC_USER_DATA=true

# Only sync data on first login
KEYCLOAK_SYNC_USER_DATA=false
```

When `SYNC_USER_DATA=true`:
- Email is updated if changed in Keycloak
- Name is updated if changed in Keycloak
- Roles are re-synchronized on every login

### Local Authentication Fallback

```env
# Allow users to login with local credentials
KEYCLOAK_ALLOW_LOCAL_AUTH=true

# Keycloak SSO only (no local auth)
KEYCLOAK_ALLOW_LOCAL_AUTH=false
```

#### Fallback on Keycloak Errors

```env
# Allow local auth when Keycloak is unavailable
KEYCLOAK_FALLBACK_ON_ERROR=true

# Block login when Keycloak is unavailable
KEYCLOAK_FALLBACK_ON_ERROR=false
```

**Use Cases:**
- **Development**: `ALLOW_LOCAL_AUTH=true` for flexibility
- **Staging**: `ALLOW_LOCAL_AUTH=true` for testing
- **Production**: `ALLOW_LOCAL_AUTH=true` for backup access
- **High Security**: `ALLOW_LOCAL_AUTH=false` for SSO-only

### HTTP Configuration

```env
# Connection timeout in seconds (default: 30)
KEYCLOAK_HTTP_TIMEOUT=30

# Increase for slow networks
KEYCLOAK_HTTP_TIMEOUT=60

# Decrease for fast networks
KEYCLOAK_HTTP_TIMEOUT=15
```

### Cache Configuration

#### User Info Caching

```env
# Enable caching of user info responses
KEYCLOAK_CACHE_USER_INFO=true

# Cache TTL in seconds (default: 300 = 5 minutes)
KEYCLOAK_CACHE_TTL=300

# Disable caching (always fetch fresh data)
KEYCLOAK_CACHE_USER_INFO=false
```

**Recommendation:**
- **High Traffic**: Enable caching with 300-600 second TTL
- **Low Traffic**: Disable caching or use short TTL (60-120 seconds)
- **Real-time Updates**: Disable caching

## Role Mapping

### Configuration File Setup

Edit `config/keycloak.php`:

```php
'role_mapping' => [
    // Keycloak Role => Krayin CRM Role Name
    'keycloak-admin' => 'Administrator',
    'keycloak-manager' => 'Manager',
    'keycloak-sales' => 'Sales Agent',
    'keycloak-support' => 'Support Agent',
],

// Default role for users without mapped roles
'default_role' => 'Sales Agent',

// Enable role mapping
'enable_role_mapping' => env('KEYCLOAK_ENABLE_ROLE_MAPPING', true),
```

### One-to-One Mapping

```php
'role_mapping' => [
    'admin' => 'Administrator',
    'user' => 'Sales Agent',
],
```

### One-to-Many Mapping

A single Keycloak role can map to multiple Krayin roles:

```php
'role_mapping' => [
    'power-user' => ['Administrator', 'Manager', 'Sales Agent'],
    'basic-user' => 'Sales Agent',
],
```

### Role Extraction Sources

Roles are extracted from these Keycloak claims (in order):

1. **Realm Roles** (`realm_access.roles`)
2. **Client Roles** (`resource_access.{client_id}.roles`)
3. **Direct Roles Claim** (`roles`)

Example Keycloak user token:
```json
{
    "realm_access": {
        "roles": ["admin", "user"]
    },
    "resource_access": {
        "krayin-crm": {
            "roles": ["crm-admin"]
        }
    },
    "roles": ["global-admin"]
}
```

### Default Role

When a user has no mappable roles, they receive the default role:

```php
'default_role' => 'Sales Agent',
```

**Important:** Ensure the default role exists in your Krayin CRM installation.

### Disabling Role Mapping

```env
KEYCLOAK_ENABLE_ROLE_MAPPING=false
```

```php
'enable_role_mapping' => false,
'default_role' => 'Sales Agent', // All users get this role
```

## Security Settings

### CSRF Protection

CSRF protection is automatically enabled for OAuth callbacks using the `state` parameter.

No configuration needed - handled automatically.

### Token Security

#### Token Encryption

Refresh tokens are automatically encrypted before storage using Laravel's encryption:

```php
// config/keycloak.php
'encrypt_tokens' => true, // Always enabled
```

#### Token Expiration

Tokens are managed automatically:
- Access tokens expire after ~5 minutes (Keycloak default)
- Refresh tokens expire after ~30 days (Keycloak default)
- Automatic refresh before expiration

### SSL/TLS Configuration

**Production:**
```env
KEYCLOAK_BASE_URL=https://keycloak.company.com  # HTTPS required
KEYCLOAK_REDIRECT_URI=https://crm.company.com/admin/auth/keycloak/callback
```

**Development:**
```env
# HTTP acceptable for development
KEYCLOAK_BASE_URL=http://localhost:8080
```

### Session Security

Configure session security in `config/session.php`:

```php
'secure' => env('SESSION_SECURE_COOKIE', true),  // HTTPS only in production
'http_only' => true,  // Prevent JavaScript access
'same_site' => 'lax',  // CSRF protection
```

## Error Handling

### Error Display

```env
# Show detailed error messages (development only)
KEYCLOAK_SHOW_ERROR_DETAILS=true

# Hide error details (production)
KEYCLOAK_SHOW_ERROR_DETAILS=false
```

### Error Logging

```env
# Log full stack traces
KEYCLOAK_LOG_STACK_TRACES=true

# Log errors only (no stack traces)
KEYCLOAK_LOG_STACK_TRACES=false
```

### Retry Mechanism

```env
# Number of retry attempts for failed requests
KEYCLOAK_MAX_RETRIES=3

# Initial retry delay in milliseconds
KEYCLOAK_RETRY_DELAY=1000

# Use exponential backoff (1s, 2s, 4s, ...)
KEYCLOAK_EXPONENTIAL_BACKOFF=true
```

**Recommended Settings:**
- **High Availability**: `MAX_RETRIES=3`, `EXPONENTIAL_BACKOFF=true`
- **Fast Failure**: `MAX_RETRIES=1`, `EXPONENTIAL_BACKOFF=false`

## Performance Tuning

### Connection Pooling

Configure HTTP client performance in `config/keycloak.php`:

```php
'http_timeout' => env('KEYCLOAK_HTTP_TIMEOUT', 30),
'verify_ssl' => env('KEYCLOAK_VERIFY_SSL', true),
```

### Cache Strategy

```php
// config/keycloak.php
'cache_user_info' => env('KEYCLOAK_CACHE_USER_INFO', true),
'cache_ttl' => env('KEYCLOAK_CACHE_TTL', 300),
```

**Performance Impact:**
- **With Cache (300s TTL)**: ~10ms response time
- **Without Cache**: ~100-200ms response time

### Database Indexes

Indexes are automatically created during migration:

```sql
-- Keycloak ID lookup (frequent)
INDEX idx_users_keycloak_id ON users(keycloak_id)

-- Auth provider filtering
INDEX idx_users_auth_provider ON users(auth_provider)

-- Combined lookup
INDEX idx_users_provider_keycloak ON users(auth_provider, keycloak_id)
```

### Queue Configuration

For better performance, queue role synchronization:

```php
// config/keycloak.php
'queue_role_sync' => env('KEYCLOAK_QUEUE_ROLE_SYNC', false),
'queue_connection' => env('KEYCLOAK_QUEUE_CONNECTION', 'sync'),
```

## Development vs Production

### Development Configuration

**.env.development:**
```env
KEYCLOAK_ENABLED=true
KEYCLOAK_BASE_URL=http://localhost:8080
KEYCLOAK_REALM=development
KEYCLOAK_SHOW_ERROR_DETAILS=true
KEYCLOAK_LOG_STACK_TRACES=true
KEYCLOAK_ALLOW_LOCAL_AUTH=true
KEYCLOAK_FALLBACK_ON_ERROR=true
KEYCLOAK_CACHE_USER_INFO=false
KEYCLOAK_HTTP_TIMEOUT=60
```

### Production Configuration

**.env.production:**
```env
KEYCLOAK_ENABLED=true
KEYCLOAK_BASE_URL=https://keycloak.company.com
KEYCLOAK_REALM=production
KEYCLOAK_SHOW_ERROR_DETAILS=false
KEYCLOAK_LOG_STACK_TRACES=true
KEYCLOAK_ALLOW_LOCAL_AUTH=true
KEYCLOAK_FALLBACK_ON_ERROR=true
KEYCLOAK_CACHE_USER_INFO=true
KEYCLOAK_CACHE_TTL=300
KEYCLOAK_HTTP_TIMEOUT=30
KEYCLOAK_MAX_RETRIES=3
```

## Configuration Validation

### Validate Configuration

```bash
php artisan tinker

# Load configuration
$config = config('keycloak');

# Check required settings
$required = ['base_url', 'realm', 'client_id', 'client_secret', 'redirect_uri'];
foreach ($required as $key) {
    echo "$key: " . ($config[$key] ?? 'NOT SET') . "\n";
}

# Test connection
$service = app(\Webkul\KeycloakSSO\Services\KeycloakService::class);
echo $service->isEnabled() ? "Enabled" : "Disabled";
```

### Common Configuration Issues

#### Issue: Invalid Redirect URI

**Symptom**: OAuth callback fails with "invalid redirect_uri"

**Solution**:
```env
# Ensure exact match with Keycloak configuration
KEYCLOAK_REDIRECT_URI=https://crm.example.com/admin/auth/keycloak/callback

# Check in Keycloak Admin:
# Clients → [Your Client] → Valid Redirect URIs
```

#### Issue: Token Expiration

**Symptom**: Frequent "token expired" errors

**Solution**:
```php
// Check Keycloak token lifespans:
// Realm Settings → Tokens → Access Token Lifespan

// Ensure automatic refresh is working
'token_refresh_threshold' => 300, // Refresh 5 min before expiry
```

#### Issue: Role Mapping Not Working

**Symptom**: Users get default role instead of mapped role

**Solution**:
```php
// Verify role mapping in config/keycloak.php
'role_mapping' => [
    'exact-keycloak-role-name' => 'Exact Krayin Role Name',
],

// Check role extraction in logs
tail -f storage/logs/laravel.log | grep "Mapped Keycloak roles"
```

## Best Practices

1. **Use Environment Variables**: Never hardcode credentials
2. **Enable Caching in Production**: Reduces Keycloak server load
3. **Keep Local Auth Enabled**: Provides backup access
4. **Use HTTPS**: Required for production security
5. **Monitor Logs**: Watch for authentication failures
6. **Regular Token Rotation**: Update client secrets periodically
7. **Test Fallback**: Verify local auth works when Keycloak is down

## Next Steps

- **Test Configuration**: [TESTING.md](tests/README.md)
- **Troubleshooting**: [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
- **API Reference**: [API_REFERENCE.md](API_REFERENCE.md)
- **Architecture**: [ARCHITECTURE.md](ARCHITECTURE.md)
