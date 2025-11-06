# Troubleshooting Guide

Common issues and solutions for Keycloak SSO Extension.

## Table of Contents

- [Installation Issues](#installation-issues)
- [Configuration Issues](#configuration-issues)
- [Authentication Issues](#authentication-issues)
- [Token Issues](#token-issues)
- [Role Mapping Issues](#role-mapping-issues)
- [Performance Issues](#performance-issues)
- [Debugging](#debugging)

## Installation Issues

### Package Not Found

**Error**: `Package webkul/laravel-keycloak-sso could not be found`

**Solutions**:
1. Verify package name and repository access
2. Clear composer cache: `composer clearcache`
3. Update composer: `composer self-update`
4. Check minimum stability in composer.json

### Migration Failures

**Error**: `SQLSTATE[42S01]: Base table or view already exists`

**Solutions**:
```bash
# Check migration status
php artisan migrate:status

# Roll back last migration
php artisan migrate:rollback

# Refresh all migrations (CAUTION: data loss)
php artisan migrate:fresh
```

### Service Provider Not Registered

**Error**: `Class 'Webkul\KeycloakSSO\...' not found`

**Solutions**:
```bash
# Regenerate autoload files
composer dump-autoload

# Clear application cache
php artisan cache:clear
php artisan config:clear

# Verify service provider registration
php artisan tinker
>>> app()->getProviders('Webkul\KeycloakSSO\Providers\KeycloakSSOServiceProvider')
```

## Configuration Issues

### Keycloak Connection Failed

**Error**: `Connection refused` or `Could not connect to Keycloak`

**Solutions**:
1. Verify Keycloak server is running
2. Check firewall rules
3. Test connectivity:
```bash
curl https://keycloak.example.com/realms/master/.well-known/openid-configuration
```
4. Verify `KEYCLOAK_BASE_URL` in `.env`
5. Check HTTP timeout setting

### Invalid Redirect URI

**Error**: `Invalid parameter: redirect_uri`

**Solutions**:
1. Ensure redirect URI matches exactly in Keycloak client config
2. Include protocol (http:// or https://)
3. No trailing slashes
4. Check Keycloak Admin Console:
   - Clients → [Your Client] → Valid Redirect URIs

```env
# Correct
KEYCLOAK_REDIRECT_URI=https://crm.example.com/admin/auth/keycloak/callback

# Incorrect
KEYCLOAK_REDIRECT_URI=https://crm.example.com/admin/auth/keycloak/callback/
KEYCLOAK_REDIRECT_URI=crm.example.com/admin/auth/keycloak/callback
```

### Configuration Not Loading

**Error**: Config values are `null` or default

**Solutions**:
```bash
# Clear configuration cache
php artisan config:clear

# Verify .env file is loaded
php artisan tinker
>>> env('KEYCLOAK_ENABLED')
>>> config('keycloak.enabled')

# Republish configuration
php artisan vendor:publish --tag=keycloak-config --force
```

## Authentication Issues

### "Login with Keycloak" Button Not Showing

**Symptoms**: SSO button missing from login page

**Solutions**:
1. Verify Keycloak is enabled:
```env
KEYCLOAK_ENABLED=true
```

2. Clear cache:
```bash
php artisan view:clear
php artisan cache:clear
```

3. Check login view integration
4. Verify routes are loaded:
```bash
php artisan route:list | grep keycloak
```

### OAuth Callback Error

**Error**: `Invalid state parameter` or `CSRF attack detected`

**Solutions**:
1. Don't open login in multiple tabs
2. Complete login within session timeout
3. Clear browser cookies and try again
4. Check session configuration in `config/session.php`

### User Not Being Provisioned

**Error**: `User not found and auto-provisioning is disabled`

**Solutions**:
1. Enable auto-provisioning:
```env
KEYCLOAK_AUTO_PROVISION=true
```

2. Or manually create user first with matching email

### Unauthorized Access

**Error**: `401 Unauthorized` or `403 Forbidden`

**Solutions**:
1. Verify user has required roles in Keycloak
2. Check role mapping configuration
3. Verify client credentials are correct
4. Ensure user is active in Keycloak

## Token Issues

### Token Expired Errors

**Error**: `Access token has expired`

**Solutions**:
1. Token refresh should be automatic - check logs
2. Verify refresh token is stored:
```sql
SELECT keycloak_refresh_token FROM users WHERE id = ?;
```

3. Check token expiration settings in Keycloak:
   - Realm Settings → Tokens → Access Token Lifespan

4. Increase refresh threshold if needed

### Token Refresh Failure

**Error**: `Failed to refresh token`

**Solutions**:
1. Check Keycloak server connectivity
2. Verify refresh token hasn't expired
3. Check HTTP timeout settings:
```env
KEYCLOAK_HTTP_TIMEOUT=30
```

4. Enable retry mechanism:
```env
KEYCLOAK_MAX_RETRIES=3
KEYCLOAK_EXPONENTIAL_BACKOFF=true
```

### Invalid Token

**Error**: `Token validation failed`

**Solutions**:
1. Verify token format
2. Check client secret matches Keycloak
3. Verify realm name is correct
4. Ensure clocks are synchronized (NTP)

## Role Mapping Issues

### Users Getting Wrong Roles

**Symptoms**: Users assigned incorrect or default roles

**Solutions**:
1. Verify role mapping in `config/keycloak.php`:
```php
'role_mapping' => [
    'exact-keycloak-role' => 'Exact Krayin Role',
],
```

2. Check user roles in Keycloak:
   - Users → [User] → Role Mappings

3. Enable debug logging:
```php
// Temporarily add to KeycloakService
Log::debug('Keycloak roles', ['roles' => $keycloakRoles]);
Log::debug('Mapped roles', ['roles' => $mappedRoles]);
```

4. Verify role names match exactly (case-sensitive)

### Default Role Not Working

**Error**: Users have no roles after login

**Solutions**:
1. Ensure default role exists in Krayin CRM
2. Check role mapping configuration:
```php
'default_role' => 'Sales Agent', // Must exist in DB
```

3. Verify role in database:
```sql
SELECT * FROM roles WHERE name = 'Sales Agent';
```

### Role Sync Not Updating

**Symptoms**: Role changes in Keycloak not reflecting in CRM

**Solutions**:
1. Enable role sync:
```env
KEYCLOAK_SYNC_USER_DATA=true
KEYCLOAK_ENABLE_ROLE_MAPPING=true
```

2. Force sync by logging out and back in
3. Check logs for sync errors

## Performance Issues

### Slow Login

**Symptoms**: Login takes > 5 seconds

**Solutions**:
1. Enable user info caching:
```env
KEYCLOAK_CACHE_USER_INFO=true
KEYCLOAK_CACHE_TTL=300
```

2. Reduce HTTP timeout for faster failure:
```env
KEYCLOAK_HTTP_TIMEOUT=15
```

3. Check network latency to Keycloak server
4. Verify database indexes exist:
```sql
SHOW INDEXES FROM users WHERE Column_name IN ('keycloak_id', 'auth_provider');
```

### High Memory Usage

**Symptoms**: PHP memory exhausted

**Solutions**:
1. Increase PHP memory limit:
```ini
memory_limit = 256M
```

2. Optimize cache storage
3. Reduce session size
4. Profile with Xdebug or Blackfire

## Debugging

### Enable Debug Logging

```env
LOG_LEVEL=debug
KEYCLOAK_SHOW_ERROR_DETAILS=true
KEYCLOAK_LOG_STACK_TRACES=true
```

### Check Logs

```bash
# Watch logs in real-time
tail -f storage/logs/laravel.log | grep Keycloak

# Filter authentication events
tail -f storage/logs/laravel.log | grep "Keycloak.*login\|authentication"

# Check error logs
grep "ERROR" storage/logs/laravel.log | grep Keycloak
```

### Test Keycloak Connection

```php
php artisan tinker

$service = app(\Webkul\KeycloakSSO\Services\KeycloakService::class);

// Test if enabled
$service->isEnabled();

// Get authorization URL
$authUrl = $service->getAuthorizationUrl();
echo $authUrl;

// Test configuration
$config = $service->getConfig('base_url');
echo $config;
```

### Test Role Mapping

```php
php artisan tinker

$mapper = app(\Webkul\KeycloakSSO\Services\RoleMappingService::class);

// Test mapping
$keycloakRoles = ['admin', 'user'];
$mappedRoles = $mapper->mapKeycloakRolesToKrayin($keycloakRoles);
print_r($mappedRoles);
```

### Verify Routes

```bash
php artisan route:list | grep keycloak
```

Expected output:
```
GET|HEAD   admin/auth/keycloak/login ....... keycloak.login
GET|HEAD   admin/auth/keycloak/callback .... keycloak.callback
POST       admin/auth/keycloak/logout ...... keycloak.logout
```

### Test Database

```sql
-- Check user provisioning
SELECT id, name, email, keycloak_id, auth_provider
FROM users
WHERE auth_provider = 'keycloak';

-- Check token storage
SELECT id, name, email,
       keycloak_id IS NOT NULL as has_keycloak_id,
       keycloak_refresh_token IS NOT NULL as has_refresh_token,
       keycloak_token_expires_at
FROM users
WHERE keycloak_id IS NOT NULL;
```

## Getting Help

If these solutions don't resolve your issue:

1. **Check Documentation**: [Wiki](https://github.com/kennis-ai/krayin-crm-keycloak/wiki)
2. **Search Issues**: [GitHub Issues](https://github.com/kennis-ai/krayin-crm-keycloak/issues)
3. **Create Issue**: Include:
   - Laravel version
   - Krayin CRM version
   - Keycloak version
   - Error messages
   - Relevant logs
   - Steps to reproduce
4. **Email Support**: suporte@kennis.com.br
