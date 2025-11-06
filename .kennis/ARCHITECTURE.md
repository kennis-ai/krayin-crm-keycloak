# Krayin CRM Keycloak SSO Extension - Architecture Documentation

## Table of Contents
1. [Overview](#overview)
2. [Architecture Principles](#architecture-principles)
3. [System Components](#system-components)
4. [Authentication Flow](#authentication-flow)
5. [Data Models](#data-models)
6. [Service Layer](#service-layer)
7. [Integration Points](#integration-points)
8. [Security Architecture](#security-architecture)
9. [Performance Considerations](#performance-considerations)

---

## Overview

This document describes the technical architecture of the Krayin CRM Keycloak SSO extension. The extension is designed as a modular package that integrates with Krayin CRM's authentication system while maintaining complete backward compatibility with existing local authentication.

### Key Design Goals

1. **Zero Core Modification**: No changes to Krayin CRM core codebase
2. **Backward Compatibility**: Existing local authentication continues to work
3. **Extensibility**: Easy to extend and customize
4. **Security First**: Enterprise-grade security standards
5. **Performance**: Minimal performance overhead
6. **Maintainability**: Clean, well-documented code

---

## Architecture Principles

### 1. Separation of Concerns
- **Controllers**: Handle HTTP requests/responses
- **Services**: Implement business logic
- **Repositories**: Manage data access
- **Events/Listeners**: Handle cross-cutting concerns

### 2. Dependency Injection
- All dependencies injected via constructor
- Laravel service container manages dependencies
- Easy to test and mock

### 3. Interface-Driven Design
- Program to interfaces, not implementations
- Allows for easy swapping of implementations
- Facilitates testing with mocks

### 4. Event-Driven Architecture
- Loose coupling between components
- Easy to extend functionality
- Clear separation of concerns

### 5. Configuration Over Code
- All settings configurable via environment
- Feature flags for gradual rollout
- No hard-coded values

---

## System Components

### Component Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        Krayin CRM Core                          │
│                     (Unmodified System)                         │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ Integrates via
                              │ Service Providers
                              │
┌─────────────────────────────▼─────────────────────────────────┐
│              Keycloak SSO Extension Package                   │
├───────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌─────────────┐  ┌──────────────┐  ┌──────────────────┐   │
│  │  Service    │  │  Controller  │  │  Middleware      │   │
│  │  Providers  │  │  Layer       │  │  Layer           │   │
│  └─────────────┘  └──────────────┘  └──────────────────┘   │
│                                                               │
│  ┌─────────────┐  ┌──────────────┐  ┌──────────────────┐   │
│  │  Services   │  │  Events &    │  │  Repositories    │   │
│  │  Layer      │  │  Listeners   │  │  Layer           │   │
│  └─────────────┘  └──────────────┘  └──────────────────┘   │
│                                                               │
└───────────────────────────────────────────────────────────────┘
                              │
                              │ OAuth 2.0 / OIDC
                              │
                        ┌─────▼──────┐
                        │  Keycloak  │
                        │   Server   │
                        └────────────┘
```

### Layer Architecture

```
┌──────────────────────────────────────────────────────┐
│          Presentation Layer (Views, Routes)          │
├──────────────────────────────────────────────────────┤
│         Application Layer (Controllers, API)         │
├──────────────────────────────────────────────────────┤
│       Business Logic Layer (Services, Events)        │
├──────────────────────────────────────────────────────┤
│        Data Access Layer (Repositories, Models)      │
├──────────────────────────────────────────────────────┤
│       Infrastructure Layer (External Services)       │
└──────────────────────────────────────────────────────┘
```

---

## Authentication Flow

### Standard Login Flow (OAuth 2.0 Authorization Code Flow)

```
┌─────────┐                 ┌─────────┐                 ┌──────────┐
│ Browser │                 │ Krayin  │                 │ Keycloak │
│  User   │                 │   CRM   │                 │  Server  │
└────┬────┘                 └────┬────┘                 └────┬─────┘
     │                           │                           │
     │ 1. Click "Login with     │                           │
     │    Keycloak"              │                           │
     ├──────────────────────────>│                           │
     │                           │                           │
     │ 2. Redirect to Keycloak   │                           │
     │    Authorization URL      │                           │
     │<──────────────────────────┤                           │
     │                           │                           │
     │ 3. User enters credentials                            │
     ├───────────────────────────────────────────────────────>│
     │                           │                           │
     │ 4. Keycloak validates     │                           │
     │    credentials            │                           │
     │                           │                           │
     │ 5. Authorization code     │                           │
     │<───────────────────────────────────────────────────────┤
     │                           │                           │
     │ 6. Callback with code     │                           │
     ├──────────────────────────>│                           │
     │                           │                           │
     │                           │ 7. Exchange code for      │
     │                           │    access token           │
     │                           ├──────────────────────────>│
     │                           │                           │
     │                           │ 8. Return tokens          │
     │                           │<──────────────────────────┤
     │                           │                           │
     │                           │ 9. Get user info          │
     │                           ├──────────────────────────>│
     │                           │                           │
     │                           │ 10. Return user data      │
     │                           │<──────────────────────────┤
     │                           │                           │
     │                           │ 11. Provision/sync user   │
     │                           │     (internal)            │
     │                           │                           │
     │ 12. Create session &      │                           │
     │     redirect to dashboard │                           │
     │<──────────────────────────┤                           │
     │                           │                           │
```

### Token Refresh Flow

```
┌─────────┐                 ┌─────────┐                 ┌──────────┐
│ Browser │                 │ Krayin  │                 │ Keycloak │
│  User   │                 │   CRM   │                 │  Server  │
└────┬────┘                 └────┬────┘                 └────┬─────┘
     │                           │                           │
     │ 1. Make authenticated      │                           │
     │    request                │                           │
     ├──────────────────────────>│                           │
     │                           │                           │
     │                           │ 2. Check token expiry     │
     │                           │    (middleware)           │
     │                           │                           │
     │                           │ 3. Token expired,         │
     │                           │    use refresh token      │
     │                           ├──────────────────────────>│
     │                           │                           │
     │                           │ 4. Return new tokens      │
     │                           │<──────────────────────────┤
     │                           │                           │
     │                           │ 5. Update session         │
     │                           │                           │
     │ 6. Continue request       │                           │
     │<──────────────────────────┤                           │
     │                           │                           │
```

### Logout Flow (Single Logout - SLO)

```
┌─────────┐                 ┌─────────┐                 ┌──────────┐
│ Browser │                 │ Krayin  │                 │ Keycloak │
│  User   │                 │   CRM   │                 │  Server  │
└────┬────┘                 └────┬────┘                 └────┬─────┘
     │                           │                           │
     │ 1. Click Logout           │                           │
     ├──────────────────────────>│                           │
     │                           │                           │
     │                           │ 2. Revoke refresh token   │
     │                           ├──────────────────────────>│
     │                           │                           │
     │                           │ 3. Token revoked          │
     │                           │<──────────────────────────┤
     │                           │                           │
     │                           │ 4. Clear local session    │
     │                           │                           │
     │ 5. Redirect to Keycloak   │                           │
     │    logout URL             │                           │
     │<──────────────────────────┤                           │
     │                           │                           │
     │ 6. Keycloak logout        │                           │
     ├───────────────────────────────────────────────────────>│
     │                           │                           │
     │ 7. Redirect to login      │                           │
     │<───────────────────────────────────────────────────────┤
     │                           │                           │
```

---

## Data Models

### Extended User Model

```php
// Database Schema Extension
users
├── id (existing)
├── name (existing)
├── email (existing)
├── password (existing, nullable)
├── keycloak_id (new, string, nullable, unique)
├── auth_provider (new, enum: 'local', 'keycloak', default: 'local')
├── keycloak_refresh_token (new, text, encrypted, nullable)
├── keycloak_token_expires_at (new, timestamp, nullable)
└── ... (other existing fields)
```

### User Authentication Flow Data

```php
// Session Data Structure
session()->put('keycloak_auth', [
    'access_token' => 'eyJhbGci...',
    'refresh_token' => 'eyJhbGci...',
    'expires_at' => 1704556800,
    'id_token' => 'eyJhbGci...',
    'user_info' => [
        'sub' => 'uuid',
        'email' => 'user@example.com',
        'name' => 'John Doe',
        'roles' => ['admin', 'user']
    ]
]);
```

---

## Service Layer

### KeycloakService

**Purpose**: Core service for interacting with Keycloak server

```php
class KeycloakService
{
    // OAuth Flow
    public function getAuthorizationUrl(): string
    public function handleCallback(Request $request): array

    // Token Management
    public function refreshToken(string $refreshToken): array
    public function validateToken(string $accessToken): bool
    public function revokeToken(string $refreshToken): bool

    // User Information
    public function getUserInfo(string $accessToken): array
    public function getUserRoles(string $accessToken): array

    // Logout
    public function getLogoutUrl(string $idToken): string
    public function logout(string $refreshToken): bool
}
```

### UserProvisioningService

**Purpose**: Handle user creation and synchronization

```php
class UserProvisioningService
{
    // User Provisioning
    public function findOrCreateUser(array $keycloakUser): User
    public function provisionUser(array $keycloakUser): User

    // User Synchronization
    public function syncUserData(User $user, array $keycloakData): User
    public function shouldSyncUser(User $user): bool

    // User Updates
    public function updateUserFromKeycloak(User $user, array $data): User
    public function updateKeycloakId(User $user, string $keycloakId): User
}
```

### RoleMappingService

**Purpose**: Map Keycloak roles to Krayin roles

```php
class RoleMappingService
{
    // Role Mapping
    public function mapKeycloakRolesToKrayin(array $keycloakRoles): array
    public function getRoleMappingConfig(): array

    // Role Assignment
    public function assignRoles(User $user, array $roles): void
    public function syncRoles(User $user, array $keycloakRoles): void

    // Role Resolution
    public function resolveDefaultRole(): string
    public function getHighestPriorityRole(array $roles): string
}
```

---

## Integration Points

### 1. Service Provider Registration

```php
// config/app.php (auto-discovered)
'providers' => [
    // ... existing providers
    Webkul\KeycloakSSO\Providers\KeycloakSSOServiceProvider::class,
]

// config/concord.php
'modules' => [
    // ... existing modules
    Webkul\KeycloakSSO\Providers\ModuleServiceProvider::class,
]
```

### 2. Route Integration

```php
// Routes are automatically loaded via service provider
// Prefix: /admin/auth/keycloak

Route::prefix('admin/auth/keycloak')->group(function () {
    Route::get('login', 'KeycloakAuthController@redirect')
        ->name('admin.keycloak.login');

    Route::get('callback', 'KeycloakAuthController@callback')
        ->name('admin.keycloak.callback');

    Route::post('logout', 'KeycloakAuthController@logout')
        ->name('admin.keycloak.logout');
});
```

### 3. View Integration

```blade
{{-- Injected into login page via event or view composer --}}
@if(config('keycloak.enabled'))
    @include('keycloak::login-button')
@endif
```

### 4. Event Hooks

```php
// Listen to Krayin events
Event::listen('admin.session.create.before', function() {
    // Optionally redirect to Keycloak
});

// Dispatch custom events
Event::dispatch(new KeycloakLoginSuccessful($user, $keycloakData));
```

### 5. Middleware Stack

```php
// Applied to admin routes
protected $routeMiddleware = [
    'keycloak.auth' => KeycloakAuthenticate::class,
    'keycloak.refresh' => KeycloakTokenRefresh::class,
];
```

---

## Security Architecture

### 1. Token Security

**Access Token Storage:**
- Stored in encrypted session
- Never exposed to client-side JavaScript
- Validated on every request

**Refresh Token Storage:**
- Encrypted in database
- Never sent to frontend
- Used only for token refresh

**ID Token:**
- Stored in session for SLO
- Contains user identity claims
- Validated signature

### 2. CSRF Protection

- Laravel CSRF tokens maintained
- State parameter in OAuth flow
- Validated on callback

### 3. SSL/TLS Requirements

- HTTPS required in production
- Secure cookies enabled
- HSTS headers recommended

### 4. Input Validation

- All external data sanitized
- Request validation classes
- Type hints enforced

### 5. Error Handling

- No sensitive data in error messages
- Errors logged securely
- Generic messages to users

### 6. Session Security

```php
// Session configuration
'secure' => env('SESSION_SECURE_COOKIE', true),
'http_only' => true,
'same_site' => 'lax',
'lifetime' => 120,
```

---

## Performance Considerations

### 1. Token Caching Strategy

```php
// Cache access token until expiry
Cache::remember("keycloak_token_{$userId}", $expiresIn, function() {
    return $this->keycloakService->getAccessToken();
});
```

### 2. User Data Sync

- Sync only when necessary (configurable)
- Async sync via jobs (optional)
- Incremental updates only

### 3. Database Optimization

```php
// Indexed columns
$table->index('keycloak_id');
$table->index('auth_provider');

// Efficient queries
User::where('keycloak_id', $keycloakId)
    ->where('auth_provider', 'keycloak')
    ->first();
```

### 4. API Call Minimization

- Batch operations where possible
- Cache user info
- Avoid redundant calls

### 5. Lazy Loading

- Services loaded only when needed
- Conditional provider registration
- Feature flag checks

---

## Error Handling Strategy

### Error Categories

1. **Network Errors**: Connection to Keycloak failed
2. **Authentication Errors**: Invalid credentials, expired tokens
3. **Authorization Errors**: Insufficient permissions
4. **Configuration Errors**: Missing or invalid config
5. **User Provisioning Errors**: User creation failed

### Fallback Mechanisms

```php
// Fallback to local auth on error
try {
    $this->keycloakService->authenticate();
} catch (KeycloakException $e) {
    if (config('keycloak.fallback_on_error')) {
        return $this->fallbackToLocalAuth();
    }
    throw $e;
}
```

### Error Logging

```php
// Structured logging
Log::error('Keycloak authentication failed', [
    'error' => $exception->getMessage(),
    'user' => $request->input('email'),
    'trace_id' => $traceId,
    'timestamp' => now(),
]);
```

---

## Monitoring and Observability

### Key Metrics

1. **Authentication Success Rate**
2. **Token Refresh Success Rate**
3. **Average Authentication Time**
4. **Keycloak API Response Times**
5. **Error Rates by Type**

### Logging Strategy

```php
// Authentication events
Log::info('Keycloak login successful', ['user_id' => $user->id]);
Log::warning('Keycloak token expired', ['user_id' => $user->id]);
Log::error('Keycloak connection failed', ['error' => $e->getMessage()]);

// Performance monitoring
Log::debug('Keycloak API call', [
    'endpoint' => '/token',
    'duration' => $duration,
]);
```

---

## Testing Strategy

### Unit Tests
- Service layer methods
- Repository methods
- Helper functions
- Validation logic

### Feature Tests
- Authentication flows
- Token management
- User provisioning
- Role mapping

### Integration Tests
- End-to-end authentication
- Keycloak server integration
- Database interactions
- Event dispatching

### Mocking Strategy

```php
// Mock Keycloak service
$mock = Mockery::mock(KeycloakService::class);
$mock->shouldReceive('handleCallback')
     ->andReturn(['access_token' => 'token']);

$this->app->instance(KeycloakService::class, $mock);
```

---

## Deployment Considerations

### Environment Variables

```env
# Keycloak Configuration
KEYCLOAK_ENABLED=true
KEYCLOAK_CLIENT_ID=krayin-crm
KEYCLOAK_CLIENT_SECRET=secret
KEYCLOAK_BASE_URL=https://keycloak.example.com
KEYCLOAK_REALM=master
KEYCLOAK_REDIRECT_URI=https://crm.example.com/admin/auth/keycloak/callback

# Feature Flags
KEYCLOAK_AUTO_PROVISION=true
KEYCLOAK_SYNC_USER_DATA=true
KEYCLOAK_ROLE_MAPPING=true
KEYCLOAK_ALLOW_LOCAL_AUTH=true
KEYCLOAK_FALLBACK_ON_ERROR=true
```

### Migration Strategy

1. Deploy package to staging
2. Test with subset of users
3. Monitor for errors
4. Gradual rollout with feature flag
5. Full production deployment

### Rollback Plan

1. Disable via feature flag
2. Users fall back to local auth
3. No data loss
4. Quick recovery

---

**Document Version**: 1.0
**Last Updated**: 2025-01-06
**Status**: Technical Design
