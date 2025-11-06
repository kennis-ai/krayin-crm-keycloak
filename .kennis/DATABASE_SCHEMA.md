# Database Schema - Keycloak SSO Integration

## Overview

This document describes the database schema changes required for Keycloak SSO integration with Krayin CRM.

## Migration

**File**: `src/Database/Migrations/2024_01_01_000001_add_keycloak_fields_to_users_table.php`

### Schema Changes

The migration adds the following columns to the `users` table:

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| `keycloak_id` | VARCHAR(255) | nullable, unique | Keycloak user unique identifier (sub claim from OpenID Connect token) |
| `auth_provider` | ENUM | ['local', 'keycloak'], default 'local' | Authentication provider used by this user |
| `keycloak_refresh_token` | TEXT | nullable | Encrypted Keycloak refresh token for automatic token renewal |
| `keycloak_token_expires_at` | TIMESTAMP | nullable | Access token expiration timestamp |

### Indexes

For optimal query performance, the following indexes are created:

- `idx_users_keycloak_id` - Index on `keycloak_id` column
- `idx_users_auth_provider` - Index on `auth_provider` column

### Column Details

#### keycloak_id

- **Purpose**: Stores the unique Keycloak user identifier (the `sub` claim from OpenID Connect)
- **Constraints**: Unique constraint ensures one-to-one mapping between Keycloak and Krayin users
- **Nullable**: Yes, allows users without Keycloak accounts
- **Usage**: Used to link Krayin users with their Keycloak identity

#### auth_provider

- **Purpose**: Tracks which authentication method the user uses
- **Values**:
  - `local`: User authenticates with Krayin's local authentication (email/password)
  - `keycloak`: User authenticates via Keycloak SSO
- **Default**: `local` (backward compatible with existing users)
- **Usage**: Determines authentication flow and available features

#### keycloak_refresh_token

- **Purpose**: Stores the encrypted OAuth2 refresh token from Keycloak
- **Encryption**: Automatically encrypted using Laravel's `Crypt` facade
- **Security**: Never exposed in API responses or logs
- **Usage**: Allows automatic token renewal without re-authentication
- **Nullable**: Yes, only populated for Keycloak users

#### keycloak_token_expires_at

- **Purpose**: Tracks when the current access token expires
- **Format**: Laravel timestamp (Carbon instance)
- **Usage**: Determines when to refresh the access token
- **Nullable**: Yes, only relevant for active Keycloak sessions

## User Model Integration

### Adding the Trait

To enable Keycloak functionality in the Krayin User model, add the `HasKeycloakAuthentication` trait:

```php
<?php

namespace Webkul\User\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Webkul\KeycloakSSO\Traits\HasKeycloakAuthentication;

class User extends Authenticatable
{
    use HasKeycloakAuthentication;

    // ... rest of the User model
}
```

### Available Methods

Once the trait is added, the following methods become available:

#### Authentication Check Methods

```php
// Check if user uses Keycloak
$user->isKeycloakUser(); // bool

// Check if user uses local auth
$user->isLocalUser(); // bool
```

#### Keycloak ID Management

```php
// Get Keycloak ID
$keycloakId = $user->getKeycloakId(); // string|null

// Set Keycloak ID
$user->setKeycloakId('keycloak-sub-claim-id');
```

#### Token Management

```php
// Get decrypted refresh token
$refreshToken = $user->getKeycloakRefreshToken(); // string|null

// Set refresh token (automatically encrypted)
$user->setKeycloakRefreshToken($token);

// Check if token has expired
$user->hasKeycloakTokenExpired(); // bool

// Check if token is expiring soon (default: 5 minutes)
$user->isKeycloakTokenExpiringSoon(); // bool
$user->isKeycloakTokenExpiringSoon(600); // 10 minutes

// Update token expiration
$user->updateKeycloakTokenExpiration(3600); // expires in 1 hour
```

#### Data Management

```php
// Clear all Keycloak data
$user->clearKeycloakData();
```

#### Query Scopes

```php
// Get only Keycloak users
$keycloakUsers = User::keycloakUsers()->get();

// Get only local users
$localUsers = User::localUsers()->get();

// Get users with expired tokens
$expiredUsers = User::withExpiredKeycloakTokens()->get();
```

## Migration Commands

### Running Migrations

```bash
# Publish migrations to your Laravel app
php artisan vendor:publish --provider="Webkul\KeycloakSSO\Providers\KeycloakSSOServiceProvider" --tag="keycloak-migrations"

# Run migrations
php artisan migrate

# Rollback (if needed)
php artisan migrate:rollback
```

### Testing Migrations

```bash
# Test migration up and down
php artisan migrate --path=/path/to/migrations
php artisan migrate:rollback --path=/path/to/migrations
```

## Data Migration

### Migrating Existing Users

If you're adding Keycloak to an existing Krayin installation with users:

1. All existing users will have `auth_provider = 'local'` (default)
2. Existing users can continue using local authentication
3. To migrate a user to Keycloak:

```php
use Webkul\User\Models\User;

$user = User::find($userId);
$user->auth_provider = 'keycloak';
$user->keycloak_id = $keycloakSubClaim;
$user->setKeycloakRefreshToken($refreshToken);
$user->updateKeycloakTokenExpiration($expiresIn);
$user->save();
```

## Security Considerations

### Refresh Token Encryption

- Refresh tokens are automatically encrypted using Laravel's application key
- Encryption happens in the `setKeycloakRefreshToken()` method
- Decryption happens in the `getKeycloakRefreshToken()` method
- If decryption fails, `null` is returned

### Token Rotation

- Refresh tokens should be rotated regularly
- Update tokens when they're refreshed by Keycloak
- Clear tokens on logout

### Index Performance

- The `keycloak_id` index enables fast lookups during authentication
- The `auth_provider` index optimizes filtering by authentication type
- Both indexes improve query performance for large user tables

## Backward Compatibility

### Existing Users

- Default `auth_provider = 'local'` ensures existing users continue working
- Nullable Keycloak fields don't affect existing user records
- Local authentication remains fully functional

### Gradual Migration

Users can be migrated to Keycloak gradually:

1. Install package and run migrations
2. Keep `KEYCLOAK_ALLOW_LOCAL_AUTH=true`
3. Users can choose login method
4. Migrate users as needed

## Database Size Considerations

### Storage Requirements

| Column | Size | Users | Total Size |
|--------|------|-------|------------|
| keycloak_id | ~36 bytes | 10,000 | ~360 KB |
| auth_provider | ~8 bytes | 10,000 | ~80 KB |
| keycloak_refresh_token | ~500 bytes | 10,000 | ~5 MB |
| keycloak_token_expires_at | ~8 bytes | 10,000 | ~80 KB |
| **Total** | | **10,000** | **~5.5 MB** |

The storage overhead is minimal for most installations.

## Troubleshooting

### Migration Fails

**Issue**: Migration fails with "Column already exists"

**Solution**: Check if columns were manually added. Run rollback first:
```bash
php artisan migrate:rollback --step=1
```

### Unique Constraint Violation

**Issue**: Duplicate `keycloak_id` error

**Solution**: Ensure Keycloak IDs are unique. Check for data corruption:
```sql
SELECT keycloak_id, COUNT(*)
FROM users
WHERE keycloak_id IS NOT NULL
GROUP BY keycloak_id
HAVING COUNT(*) > 1;
```

### Encryption Errors

**Issue**: Cannot decrypt refresh token

**Solution**:
1. Check `APP_KEY` hasn't changed
2. Re-authenticate user to get new token
3. Clear old encrypted data if key rotation occurred

## Future Schema Changes

Potential future additions (not in Phase 3):

- `keycloak_user_data` (JSON) - Store additional Keycloak user attributes
- `keycloak_roles` (JSON) - Cache Keycloak roles
- `last_keycloak_sync` (TIMESTAMP) - Track last synchronization
- `keycloak_groups` (JSON) - Store Keycloak group memberships

These will be added in future versions as needed.
