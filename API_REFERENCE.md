# API Reference

Complete API documentation for Keycloak SSO Extension services and methods.

## Table of Contents

- [KeycloakService](#keycloakservice)
- [UserProvisioningService](#userprovisioningservice)
- [RoleMappingService](#rolemappingservice)
- [ErrorHandler](#errorhandler)
- [Events](#events)
- [Middleware](#middleware)

## KeycloakService

Main service for Keycloak OAuth2/OpenID Connect integration.

### Constructor

```php
public function __construct(array $config)
```

**Parameters:**
- `$config` (array): Keycloak configuration array

**Throws:**
- `KeycloakConfigurationException`: If required configuration is missing or invalid

**Example:**
```php
$service = new KeycloakService(config('keycloak'));
```

### getAuthorizationUrl()

Generates the authorization URL for redirecting users to Keycloak login.

```php
public function getAuthorizationUrl(?string $state = null, array $scopes = ['openid', 'profile', 'email']): string
```

**Parameters:**
- `$state` (string|null): CSRF state parameter (auto-generated if null)
- `$scopes` (array): OAuth scopes to request

**Returns:** string - Full authorization URL

**Example:**
```php
$authUrl = $service->getAuthorizationUrl();
return redirect()->away($authUrl);
```

### handleCallback()

Processes the OAuth callback and exchanges authorization code for tokens.

```php
public function handleCallback(Request $request): array
```

**Parameters:**
- `$request` (Request): Laravel HTTP request object

**Returns:** array - Contains `tokens` and `user` data

```php
[
    'tokens' => [
        'access_token' => string,
        'refresh_token' => string,
        'expires_in' => int,
        'token_type' => 'Bearer',
    ],
    'user' => [
        'sub' => string,
        'email' => string,
        'name' => string,
        // ... other claims
    ]
]
```

**Throws:**
- `KeycloakAuthenticationException`: On authentication failure

**Example:**
```php
$data = $service->handleCallback($request);
$user = $provisioningService->findOrCreateUser($data['user']);
```

### getUserInfo()

Retrieves user information from Keycloak using access token.

```php
public function getUserInfo(string $accessToken): array
```

**Parameters:**
- `$accessToken` (string): Valid access token

**Returns:** array - User claims

**Caching:** Respects `cache_user_info` configuration

**Example:**
```php
$userInfo = $service->getUserInfo($accessToken);
echo $userInfo['email'];
```

### refreshToken()

Refreshes an access token using a refresh token.

```php
public function refreshToken(string $refreshToken): array
```

**Parameters:**
- `$refreshToken` (string): Valid refresh token

**Returns:** array - New token set

**Throws:**
- `KeycloakTokenException`: On refresh failure

**Example:**
```php
$newTokens = $service->refreshToken($user->getKeycloakRefreshToken());
```

### validateToken()

Validates an access token via introspection.

```php
public function validateToken(string $accessToken): bool
```

**Parameters:**
- `$accessToken` (string): Token to validate

**Returns:** bool - True if valid, false otherwise

**Example:**
```php
if ($service->validateToken($accessToken)) {
    // Token is valid
}
```

### logout()

Revokes refresh token on Keycloak (Single Logout).

```php
public function logout(string $refreshToken): bool
```

**Parameters:**
- `$refreshToken` (string): Token to revoke

**Returns:** bool - Success status

**Example:**
```php
$service->logout($user->getKeycloakRefreshToken());
```

### getUserRoles()

Extracts user roles from access token.

```php
public function getUserRoles(string $accessToken): array
```

**Parameters:**
- `$accessToken` (string): Valid access token

**Returns:** array - List of role names

**Example:**
```php
$roles = $service->getUserRoles($accessToken);
// ['admin', 'user', 'manager']
```

## UserProvisioningService

Handles automatic user creation and synchronization.

### Constructor

```php
public function __construct(
    RoleMappingService $roleMappingService,
    KeycloakService $keycloakService,
    bool $autoProvision = true,
    bool $syncUserData = true
)
```

### findOrCreateUser()

Finds existing user or creates new one from Keycloak data.

```php
public function findOrCreateUser(array $keycloakUser): User
```

**Parameters:**
- `$keycloakUser` (array): Keycloak user data with claims

**Returns:** User - Krayin CRM user model

**Throws:**
- `KeycloakUserProvisioningException`: On provisioning failure

**Logic:**
1. Search by `keycloak_id`
2. If not found, search by `email`
3. If not found and auto-provision enabled, create new user
4. Sync user data if enabled
5. Map and assign roles

**Example:**
```php
$user = $provisioningService->findOrCreateUser($keycloakUserData);
Auth::login($user);
```

### provisionUser()

Creates a new user from Keycloak data.

```php
public function provisionUser(array $keycloakUser): User
```

**Parameters:**
- `$keycloakUser` (array): Keycloak user claims

**Returns:** User - Newly created user

**Required Claims:**
- `sub` - Keycloak user ID
- `email` - User email

**Optional Claims:**
- `name` - Full name
- `given_name` - First name
- `family_name` - Last name
- `preferred_username` - Username

**Example:**
```php
$user = $provisioningService->provisionUser($keycloakUserData);
```

### updateUserFromKeycloak()

Updates existing user with fresh Keycloak data.

```php
public function updateUserFromKeycloak(User $user, array $keycloakData): User
```

**Parameters:**
- `$user` (User): Existing user model
- `$keycloakData` (array): Fresh Keycloak claims

**Returns:** User - Updated user model

**Updates:**
- Email (if changed)
- Name (if changed)
- Auth provider
- Keycloak ID
- Roles

**Example:**
```php
$user = $provisioningService->updateUserFromKeycloak($user, $keycloakData);
```

### Configuration Methods

```php
// Check if auto-provision is enabled
public function isAutoProvisionEnabled(): bool

// Enable/disable auto-provision
public function setAutoProvisionEnabled(bool $enabled): self

// Check if data sync is enabled
public function isSyncUserDataEnabled(): bool

// Enable/disable data sync
public function setSyncUserDataEnabled(bool $enabled): self
```

## RoleMappingService

Maps Keycloak roles to Krayin CRM roles.

### Constructor

```php
public function __construct(
    array $roleMapping = [],
    string $defaultRole = 'Sales',
    bool $syncRoles = true
)
```

### mapKeycloakRolesToKrayin()

Maps Keycloak role names to Krayin CRM role names.

```php
public function mapKeycloakRolesToKrayin(array $keycloakRoles): array
```

**Parameters:**
- `$keycloakRoles` (array): Keycloak role names

**Returns:** array - Krayin CRM role names

**Example:**
```php
$krayinRoles = $mapper->mapKeycloakRolesToKrayin(['admin', 'user']);
// ['Administrator', 'Sales Agent']
```

### syncRoles()

Synchronizes user roles from Keycloak.

```php
public function syncRoles(User $user, array $keycloakRoles): void
```

**Parameters:**
- `$user` (User): User to update
- `$keycloakRoles` (array): Keycloak roles

**Side Effects:**
- Updates user's `role_id`
- Syncs roles relationship if available

**Example:**
```php
$mapper->syncRoles($user, ['admin', 'manager']);
```

### assignRoles()

Assigns roles to a user (replaces existing).

```php
public function assignRoles(User $user, array $roleNames): void
```

**Parameters:**
- `$user` (User): User to update
- `$roleNames` (array): Krayin CRM role names

**Example:**
```php
$mapper->assignRoles($user, ['Administrator', 'Manager']);
```

## ErrorHandler

Centralized error handling utility.

### handle()

Handles and logs exceptions with sanitization.

```php
public static function handle(
    Throwable $exception,
    string $context,
    array $additionalData = []
): void
```

**Parameters:**
- `$exception` (Throwable): Exception to handle
- `$context` (string): Context description
- `$additionalData` (array): Additional log data

**Side Effects:**
- Logs error with appropriate level
- Sanitizes sensitive data automatically

**Example:**
```php
try {
    $service->doSomething();
} catch (KeycloakException $e) {
    ErrorHandler::handle($e, 'Failed to authenticate', [
        'user_id' => $user->id,
    ]);
    throw $e;
}
```

### getUserMessage()

Generates user-friendly error message.

```php
public static function getUserMessage(
    Throwable $exception,
    bool $includeDebugInfo = false
): string
```

**Parameters:**
- `$exception` (Throwable): Exception object
- `$includeDebugInfo` (bool): Include debug details

**Returns:** string - Localized error message

**Example:**
```php
$userMessage = ErrorHandler::getUserMessage($exception);
return redirect()->back()->with('error', $userMessage);
```

### retry()

Retries a callback with exponential backoff.

```php
public static function retry(
    callable $callback,
    ?int $maxAttempts = null,
    ?int $baseDelay = null
)
```

**Parameters:**
- `$callback` (callable): Function to retry
- `$maxAttempts` (int|null): Max retry attempts
- `$baseDelay` (int|null): Base delay in milliseconds

**Returns:** mixed - Callback return value

**Example:**
```php
$result = ErrorHandler::retry(function () use ($client) {
    return $client->makeRequest();
});
```

## Events

### KeycloakLoginSuccessful

Fired after successful Keycloak authentication.

```php
use Webkul\KeycloakSSO\Events\KeycloakLoginSuccessful;

event(new KeycloakLoginSuccessful($user, $keycloakUserData));
```

**Properties:**
- `$user` (User): Authenticated user
- `$keycloakUser` (array): Keycloak user claims

### KeycloakLoginFailed

Fired when Keycloak authentication fails.

```php
use Webkul\KeycloakSSO\Events\KeycloakLoginFailed;

event(new KeycloakLoginFailed($exception, $requestData));
```

**Properties:**
- `$exception` (Throwable): Error that occurred
- `$requestData` (array): Request parameters

### KeycloakLogoutSuccessful

Fired after successful logout.

```php
use Webkul\KeycloakSSO\Events\KeycloakLogoutSuccessful;

event(new KeycloakLogoutSuccessful($user));
```

**Properties:**
- `$user` (User): Logged out user

## Middleware

### KeycloakAuthenticate

Validates Keycloak session.

```php
Route::middleware(['web', 'keycloak.auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

**Behavior:**
- Validates access token
- Redirects to login if invalid
- Supports local authentication fallback

### KeycloakTokenRefresh

Automatically refreshes expiring tokens.

```php
Route::middleware(['web', 'keycloak.refresh'])->group(function () {
    // Routes that need automatic token refresh
});
```

**Behavior:**
- Checks token expiration
- Refreshes if within threshold (5 minutes)
- Updates session with new tokens

## User Model Methods

Methods added to User model via `HasKeycloakAuthentication` trait.

```php
// Check if user uses Keycloak
$user->isKeycloakUser(): bool

// Get Keycloak ID
$user->getKeycloakId(): ?string

// Get refresh token (decrypted)
$user->getKeycloakRefreshToken(): ?string

// Set refresh token (encrypted)
$user->setKeycloakRefreshToken(string $token): void

// Check if token expired
$user->isKeycloakTokenExpired(): bool

// Update token expiration
$user->updateKeycloakTokenExpiration(int $expiresIn): void

// Clear Keycloak data
$user->clearKeycloakData(): void
```

## Configuration Reference

Access configuration values:

```php
// Get Keycloak service
$service = app(\Webkul\KeycloakSSO\Services\KeycloakService::class);

// Check if enabled
$service->isEnabled();

// Get configuration value
$service->getConfig('client_id');
$service->getConfig('base_url');
```

## Helper Functions

```php
// Check if Keycloak is enabled
keycloak_enabled(): bool

// Get Keycloak login URL
keycloak_login_url(): string

// Get Keycloak logout URL
keycloak_logout_url(): string
```
