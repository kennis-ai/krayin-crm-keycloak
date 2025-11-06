# Krayin CRM Keycloak SSO Extension - API Reference

## Table of Contents
1. [Services](#services)
2. [Controllers](#controllers)
3. [Middleware](#middleware)
4. [Events](#events)
5. [Configuration](#configuration)
6. [Helper Functions](#helper-functions)

---

## Services

### KeycloakService

Core service for Keycloak integration.

#### `getAuthorizationUrl(): string`

Generates the Keycloak authorization URL for OAuth login.

**Returns**: `string` - The full authorization URL

**Example**:
```php
$url = app(KeycloakService::class)->getAuthorizationUrl();
// Returns: https://keycloak.example.com/auth/realms/master/protocol/openid-connect/auth?client_id=...
```

---

#### `handleCallback(Request $request): array`

Handles the OAuth callback from Keycloak and exchanges authorization code for tokens.

**Parameters**:
- `$request` (Request) - The HTTP request containing authorization code

**Returns**: `array` - Token response containing:
```php
[
    'access_token' => 'eyJhbGci...',
    'refresh_token' => 'eyJhbGci...',
    'expires_in' => 300,
    'id_token' => 'eyJhbGci...',
]
```

**Throws**:
- `KeycloakAuthenticationException` - If code exchange fails

**Example**:
```php
try {
    $tokens = app(KeycloakService::class)->handleCallback($request);
} catch (KeycloakAuthenticationException $e) {
    Log::error('Callback failed: ' . $e->getMessage());
}
```

---

#### `getUserInfo(string $accessToken): array`

Retrieves user information from Keycloak using an access token.

**Parameters**:
- `$accessToken` (string) - Valid Keycloak access token

**Returns**: `array` - User information:
```php
[
    'sub' => 'uuid',
    'email' => 'user@example.com',
    'name' => 'John Doe',
    'preferred_username' => 'johndoe',
    'email_verified' => true,
]
```

**Throws**:
- `KeycloakConnectionException` - If API call fails

**Example**:
```php
$userInfo = app(KeycloakService::class)->getUserInfo($accessToken);
```

---

#### `refreshToken(string $refreshToken): array`

Refreshes an expired access token using the refresh token.

**Parameters**:
- `$refreshToken` (string) - Valid refresh token

**Returns**: `array` - New token set

**Throws**:
- `KeycloakTokenExpiredException` - If refresh token is expired

**Example**:
```php
$newTokens = app(KeycloakService::class)->refreshToken($refreshToken);
```

---

#### `validateToken(string $accessToken): bool`

Validates if an access token is still valid.

**Parameters**:
- `$accessToken` (string) - Token to validate

**Returns**: `bool` - True if valid, false otherwise

**Example**:
```php
if (app(KeycloakService::class)->validateToken($token)) {
    // Token is valid
}
```

---

#### `getUserRoles(string $accessToken): array`

Retrieves user roles from Keycloak.

**Parameters**:
- `$accessToken` (string) - Valid access token

**Returns**: `array` - Array of role names

**Example**:
```php
$roles = app(KeycloakService::class)->getUserRoles($token);
// ['admin', 'manager', 'user']
```

---

#### `logout(string $refreshToken): bool`

Revokes the refresh token and logs out from Keycloak.

**Parameters**:
- `$refreshToken` (string) - Refresh token to revoke

**Returns**: `bool` - True if successful

**Example**:
```php
app(KeycloakService::class)->logout($refreshToken);
```

---

#### `getLogoutUrl(string $idToken): string`

Generates Keycloak logout URL for Single Logout.

**Parameters**:
- `$idToken` (string) - ID token from authentication

**Returns**: `string` - Logout URL

**Example**:
```php
$logoutUrl = app(KeycloakService::class)->getLogoutUrl($idToken);
```

---

### UserProvisioningService

Handles user provisioning and synchronization.

#### `findOrCreateUser(array $keycloakUser): User`

Finds existing user or creates new one from Keycloak data.

**Parameters**:
- `$keycloakUser` (array) - User data from Keycloak

**Returns**: `User` - Krayin user model

**Throws**:
- `KeycloakUserProvisioningException` - If user creation fails

**Example**:
```php
$user = app(UserProvisioningService::class)->findOrCreateUser([
    'sub' => 'keycloak-uuid',
    'email' => 'user@example.com',
    'name' => 'John Doe',
]);
```

---

#### `syncUserData(User $user, array $keycloakData): User`

Synchronizes user data from Keycloak to Krayin.

**Parameters**:
- `$user` (User) - Krayin user model
- `$keycloakData` (array) - Latest data from Keycloak

**Returns**: `User` - Updated user model

**Example**:
```php
$updatedUser = app(UserProvisioningService::class)
    ->syncUserData($user, $keycloakData);
```

---

### RoleMappingService

Handles role mapping between Keycloak and Krayin.

#### `mapKeycloakRolesToKrayin(array $keycloakRoles): array`

Maps Keycloak roles to Krayin role IDs.

**Parameters**:
- `$keycloakRoles` (array) - Array of Keycloak role names

**Returns**: `array` - Array of Krayin role IDs

**Example**:
```php
$krayinRoles = app(RoleMappingService::class)
    ->mapKeycloakRolesToKrayin(['keycloak_admin', 'keycloak_user']);
// [1, 3]
```

---

#### `assignRoles(User $user, array $roles): void`

Assigns roles to a user.

**Parameters**:
- `$user` (User) - User model
- `$roles` (array) - Role IDs to assign

**Example**:
```php
app(RoleMappingService::class)->assignRoles($user, [1, 2]);
```

---

## Controllers

### KeycloakAuthController

Handles Keycloak authentication flow.

#### `redirect(): RedirectResponse`

Redirects user to Keycloak login page.

**Route**: `GET /admin/auth/keycloak/login`
**Name**: `admin.keycloak.login`

**Returns**: `RedirectResponse` - Redirect to Keycloak

**Example**:
```php
return redirect()->route('admin.keycloak.login');
```

---

#### `callback(Request $request): RedirectResponse`

Handles OAuth callback from Keycloak.

**Route**: `GET /admin/auth/keycloak/callback`
**Name**: `admin.keycloak.callback`

**Parameters**:
- `$request` (Request) - Contains `code` and `state` parameters

**Returns**: `RedirectResponse` - Redirect to dashboard or login

**Example**:
```
GET /admin/auth/keycloak/callback?code=AUTH_CODE&state=STATE_TOKEN
```

---

#### `logout(Request $request): RedirectResponse`

Logs out user from both Krayin and Keycloak.

**Route**: `POST /admin/auth/keycloak/logout`
**Name**: `admin.keycloak.logout`
**Middleware**: `auth:user`

**Returns**: `RedirectResponse` - Redirect to Keycloak logout

**Example**:
```php
return redirect()->route('admin.keycloak.logout');
```

---

## Middleware

### KeycloakAuthenticate

Ensures user has valid Keycloak authentication.

**Usage**:
```php
Route::middleware('keycloak.auth')->group(function () {
    // Protected routes
});
```

**Behavior**:
- Checks for valid session
- Validates access token
- Redirects to login if unauthenticated

---

### KeycloakTokenRefresh

Automatically refreshes expired access tokens.

**Usage**:
```php
Route::middleware(['auth:user', 'keycloak.refresh'])->group(function () {
    // Routes with auto token refresh
});
```

**Behavior**:
- Checks token expiry
- Refreshes if expired
- Updates session with new token

---

## Events

### KeycloakLoginSuccessful

Fired when user successfully logs in via Keycloak.

**Properties**:
```php
public User $user;
public array $keycloakData;
```

**Listening**:
```php
Event::listen(KeycloakLoginSuccessful::class, function ($event) {
    Log::info('User logged in', ['user_id' => $event->user->id]);
});
```

---

### KeycloakLoginFailed

Fired when Keycloak login fails.

**Properties**:
```php
public Exception $exception;
public array $context;
```

**Listening**:
```php
Event::listen(KeycloakLoginFailed::class, function ($event) {
    Log::error('Login failed', ['error' => $event->exception->getMessage()]);
});
```

---

### KeycloakLogoutSuccessful

Fired when user successfully logs out.

**Properties**:
```php
public User $user;
```

**Listening**:
```php
Event::listen(KeycloakLogoutSuccessful::class, function ($event) {
    // Clean up user-specific data
});
```

---

## Configuration

### Config Keys

All configuration is in `config/keycloak.php`:

#### `keycloak.enabled`
Type: `bool`
Default: `false`
Enable/disable Keycloak SSO

#### `keycloak.client_id`
Type: `string`
Keycloak client ID

#### `keycloak.client_secret`
Type: `string`
Keycloak client secret

#### `keycloak.base_url`
Type: `string`
Keycloak server base URL

#### `keycloak.realm`
Type: `string`
Default: `master`
Keycloak realm name

#### `keycloak.auto_provision_users`
Type: `bool`
Default: `true`
Automatically create users from Keycloak

#### `keycloak.sync_user_data`
Type: `bool`
Default: `true`
Sync user data on each login

#### `keycloak.enable_role_mapping`
Type: `bool`
Default: `true`
Enable role mapping from Keycloak

#### `keycloak.allow_local_auth`
Type: `bool`
Default: `true`
Allow fallback to local authentication

#### `keycloak.role_mapping`
Type: `array`
Maps Keycloak roles to Krayin roles

**Example**:
```php
'role_mapping' => [
    'keycloak_admin' => 'Administrator',
    'keycloak_manager' => 'Manager',
    'keycloak_user' => 'Sales',
],
```

---

## Helper Functions

### `keycloak_enabled(): bool`

Check if Keycloak SSO is enabled.

**Example**:
```php
if (keycloak_enabled()) {
    // Show Keycloak login button
}
```

---

### `keycloak_user(User $user): bool`

Check if user is authenticated via Keycloak.

**Parameters**:
- `$user` (User) - User to check

**Returns**: `bool`

**Example**:
```php
if (keycloak_user(auth()->user())) {
    // User logged in via Keycloak
}
```

---

### `keycloak_logout_url(): string`

Get Keycloak logout URL for current user.

**Returns**: `string`

**Example**:
```php
$logoutUrl = keycloak_logout_url();
```

---

## Error Codes

### HTTP Status Codes

- `200` - Success
- `302` - Redirect (normal flow)
- `400` - Bad Request (invalid parameters)
- `401` - Unauthorized (authentication failed)
- `403` - Forbidden (authorization failed)
- `500` - Server Error (Keycloak connection failed)

### Custom Error Codes

- `KEYCLOAK_001` - Connection to Keycloak failed
- `KEYCLOAK_002` - Invalid authorization code
- `KEYCLOAK_003` - Token refresh failed
- `KEYCLOAK_004` - User provisioning failed
- `KEYCLOAK_005` - Role mapping failed
- `KEYCLOAK_006` - Invalid configuration

---

## Rate Limiting

The following endpoints are rate-limited:

- `/admin/auth/keycloak/callback` - 10 requests per minute
- `/admin/auth/keycloak/logout` - 20 requests per minute

---

## Testing

### Mocking KeycloakService

```php
use Webkul\KeycloakSSO\Services\KeycloakService;

$mock = Mockery::mock(KeycloakService::class);
$mock->shouldReceive('handleCallback')
     ->once()
     ->andReturn([
         'access_token' => 'test_token',
         'refresh_token' => 'test_refresh',
         'expires_in' => 300,
     ]);

$this->app->instance(KeycloakService::class, $mock);
```

---

**Document Version**: 1.0
**Last Updated**: 2025-01-06
**Status**: API Reference
