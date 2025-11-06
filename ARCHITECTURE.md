# Architecture Documentation

Technical architecture and design documentation for Keycloak SSO Extension.

## Table of Contents

- [Overview](#overview)
- [System Architecture](#system-architecture)
- [Component Diagrams](#component-diagrams)
- [Sequence Diagrams](#sequence-diagrams)
- [Database Schema](#database-schema)
- [Security Architecture](#security-architecture)
- [Design Patterns](#design-patterns)

## Overview

The Keycloak SSO Extension integrates Krayin CRM with Keycloak identity provider using OAuth 2.0 / OpenID Connect protocol. The architecture follows Laravel best practices and implements a service-oriented design.

### Key Architectural Principles

1. **Separation of Concerns**: Services, controllers, and middleware have distinct responsibilities
2. **Dependency Injection**: All dependencies injected through constructors
3. **Event-Driven**: Authentication events for extensibility
4. **Error Handling**: Centralized error handling with fallback support
5. **Security First**: Encrypted tokens, CSRF protection, secure sessions

## System Architecture

```mermaid
graph TB
    User[User Browser]
    Krayin[Krayin CRM]
    Keycloak[Keycloak Server]

    User -->|1. Access CRM| Krayin
    Krayin -->|2. Redirect to Login| Keycloak
    Keycloak -->|3. Authenticate| User
    User -->|4. Authorize| Keycloak
    Keycloak -->|5. OAuth Callback| Krayin
    Krayin -->|6. Exchange Code for Token| Keycloak
    Keycloak -->|7. Return Tokens & User Info| Krayin
    Krayin -->|8. Provision/Update User| Krayin
    Krayin -->|9. Create Session| User
```

## Component Diagrams

### High-Level Components

```mermaid
graph LR
    subgraph "Krayin CRM"
        Controller[KeycloakAuthController]
        Service[KeycloakService]
        Provisioning[UserProvisioningService]
        RoleMapping[RoleMappingService]
        ErrorHandler[ErrorHandler]
        Middleware[Middleware]
    end

    subgraph "External"
        Keycloak[Keycloak Server]
        Database[(Database)]
    end

    Controller --> Service
    Controller --> Provisioning
    Service --> Keycloak
    Provisioning --> RoleMapping
    Provisioning --> Database
    Middleware --> Service
    Service --> ErrorHandler
```

### Service Layer Architecture

```mermaid
classDiagram
    class KeycloakService {
        -KeycloakClient client
        -array config
        +getAuthorizationUrl() string
        +handleCallback(Request) array
        +getUserInfo(string) array
        +refreshToken(string) array
        +validateToken(string) bool
        +logout(string) bool
        +getUserRoles(string) array
    }

    class UserProvisioningService {
        -RoleMappingService roleMappingService
        -KeycloakService keycloakService
        +findOrCreateUser(array) User
        +provisionUser(array) User
        +updateUserFromKeycloak(User, array) User
        +syncUserData(User, array) User
    }

    class RoleMappingService {
        -array roleMapping
        -string defaultRole
        +mapKeycloakRolesToKrayin(array) array
        +syncRoles(User, array) void
        +assignRoles(User, array) void
    }

    class KeycloakClient {
        -string baseUrl
        -string realm
        +getTokens(string) array
        +getUserInfo(string) array
        +refreshToken(string) array
        +introspectToken(string) array
        +logout(string) bool
    }

    KeycloakService --> KeycloakClient
    UserProvisioningService --> RoleMappingService
    UserProvisioningService --> KeycloakService
```

## Sequence Diagrams

### Complete Authentication Flow

```mermaid
sequenceDiagram
    actor User
    participant Browser
    participant Controller as KeycloakAuthController
    participant Service as KeycloakService
    participant Provisioning as UserProvisioningService
    participant RoleMapper as RoleMappingService
    participant Keycloak as Keycloak Server
    participant DB as Database

    User->>Browser: Click "Login with Keycloak"
    Browser->>Controller: GET /admin/auth/keycloak/login
    Controller->>Service: getAuthorizationUrl()
    Service->>Service: Generate CSRF state
    Service->>Browser: Redirect to Keycloak

    Browser->>Keycloak: GET /auth?client_id=...&state=...
    Keycloak->>User: Show login form
    User->>Keycloak: Submit credentials
    Keycloak->>Keycloak: Authenticate user
    Keycloak->>Browser: Redirect with code & state

    Browser->>Controller: GET /callback?code=...&state=...
    Controller->>Service: handleCallback(request)
    Service->>Service: Validate CSRF state
    Service->>Keycloak: POST /token (exchange code)
    Keycloak->>Service: Return tokens & user info

    Service->>Controller: Return {tokens, user}
    Controller->>Provisioning: findOrCreateUser(keycloakUser)

    alt User doesn't exist
        Provisioning->>DB: Create new user
    else User exists
        Provisioning->>DB: Update user data
    end

    Provisioning->>RoleMapper: syncRoles(user, keycloakRoles)
    RoleMapper->>RoleMapper: Map Keycloak roles
    RoleMapper->>DB: Assign roles to user

    Provisioning->>Controller: Return User
    Controller->>DB: Store refresh token
    Controller->>Browser: Create session & redirect
    Browser->>User: Show dashboard
```

### Token Refresh Flow

```mermaid
sequenceDiagram
    participant Middleware as KeycloakTokenRefresh
    participant User
    participant Service as KeycloakService
    participant Keycloak
    participant Session

    User->>Middleware: Make request
    Middleware->>Session: Get access token
    Middleware->>Middleware: Check expiration

    alt Token expired or expiring soon
        Middleware->>Session: Get refresh token
        Middleware->>Service: refreshToken(refreshToken)
        Service->>Keycloak: POST /token (grant_type=refresh_token)
        Keycloak->>Service: Return new tokens
        Service->>Middleware: Return new tokens
        Middleware->>Session: Update tokens
        Middleware->>User: Continue with request
    else Token still valid
        Middleware->>User: Continue with request
    end
```

### Logout Flow (Single Logout)

```mermaid
sequenceDiagram
    actor User
    participant Controller as KeycloakAuthController
    participant Service as KeycloakService
    participant Keycloak
    participant DB as Database
    participant Session

    User->>Controller: POST /logout
    Controller->>DB: Get user & refresh token

    alt Is Keycloak user
        Controller->>Service: logout(refreshToken)
        Service->>Keycloak: POST /logout (revoke token)
        Keycloak->>Service: Token revoked
        Controller->>DB: Clear Keycloak data
    end

    Controller->>Session: Invalidate session
    Controller->>Session: Regenerate token

    alt Is Keycloak user
        Controller->>User: Redirect to Keycloak logout
    else Local user
        Controller->>User: Redirect to login page
    end
```

### User Provisioning Decision Flow

```mermaid
flowchart TD
    Start[Keycloak Callback] --> GetUser[Get Keycloak User Data]
    GetUser --> CheckByID{User exists<br/>by keycloak_id?}

    CheckByID -->|Yes| UpdateUser[Update User Data]
    CheckByID -->|No| CheckByEmail{User exists<br/>by email?}

    CheckByEmail -->|Yes| CheckProvider{Different<br/>auth provider?}
    CheckProvider -->|Yes| Error[Throw Duplicate Error]
    CheckProvider -->|No| LinkAccount[Link Keycloak Account]

    CheckByEmail -->|No| CheckAutoProvision{Auto-provision<br/>enabled?}
    CheckAutoProvision -->|Yes| CreateUser[Create New User]
    CheckAutoProvision -->|No| ErrorNoProvision[Throw Provisioning Error]

    LinkAccount --> UpdateUser
    CreateUser --> SyncRoles[Sync Roles]
    UpdateUser --> SyncRoles
    SyncRoles --> End[Return User]
```

## Database Schema

### Users Table Extensions

```mermaid
erDiagram
    users {
        bigint id PK
        string name
        string email UK
        string password
        string keycloak_id UK "Keycloak user ID"
        enum auth_provider "keycloak|local"
        text keycloak_refresh_token "Encrypted refresh token"
        timestamp keycloak_token_expires_at "Token expiration"
        timestamp created_at
        timestamp updated_at
    }

    roles {
        bigint id PK
        string name UK
        string description
    }

    users }o--|| roles : "has primary role"
```

### Indexes

```sql
-- Primary keycloak_id lookup
CREATE INDEX idx_users_keycloak_id ON users(keycloak_id);

-- Auth provider filtering
CREATE INDEX idx_users_auth_provider ON users(auth_provider);

-- Combined lookup optimization
CREATE INDEX idx_users_provider_keycloak ON users(auth_provider, keycloak_id);
```

## Security Architecture

### Token Security

```mermaid
flowchart LR
    subgraph "Token Lifecycle"
        A[Receive Tokens] --> B[Encrypt Refresh Token]
        B --> C[Store in Database]
        C --> D[Access Token in Session]
        D --> E[Use for API Calls]
        E --> F{Token<br/>Expired?}
        F -->|Yes| G[Decrypt Refresh Token]
        G --> H[Request New Tokens]
        H --> A
        F -->|No| E
    end
```

### CSRF Protection

```mermaid
sequenceDiagram
    participant Browser
    participant Controller
    participant Session
    participant Keycloak

    Browser->>Controller: Request login URL
    Controller->>Controller: Generate random state
    Controller->>Session: Store state
    Controller->>Browser: Redirect with state parameter

    Browser->>Keycloak: Login with state
    Keycloak->>Browser: Callback with state

    Browser->>Controller: Callback request with state
    Controller->>Session: Retrieve stored state
    Controller->>Controller: Compare states

    alt States match
        Controller->>Controller: Continue authentication
    else States don't match
        Controller->>Browser: Reject (CSRF attack)
    end
```

### Security Layers

```mermaid
graph TB
    subgraph "Application Security"
        CSRF[CSRF Protection]
        SSL[SSL/TLS]
        Encryption[Token Encryption]
        Session[Secure Sessions]
    end

    subgraph "Keycloak Security"
        OAuth[OAuth 2.0]
        OIDC[OpenID Connect]
        JWT[JWT Tokens]
    end

    subgraph "Network Security"
        Firewall[Firewall Rules]
        HTTPS[HTTPS Only]
    end

    CSRF --> OAuth
    SSL --> HTTPS
    Encryption --> JWT
    Session --> OIDC
```

## Design Patterns

### Service Layer Pattern

```php
// Services encapsulate business logic
class KeycloakService {
    public function handleCallback(Request $request): array {
        // Business logic for OAuth callback
    }
}

// Controllers orchestrate services
class KeycloakAuthController {
    public function callback(Request $request) {
        $data = $this->keycloakService->handleCallback($request);
        $user = $this->provisioningService->findOrCreateUser($data['user']);
        Auth::login($user);
    }
}
```

### Repository Pattern (Implicit)

```php
// Eloquent models act as repositories
class User extends Model {
    public static function findByKeycloakId(string $id): ?User {
        return static::where('keycloak_id', $id)->first();
    }
}
```

### Strategy Pattern (Role Mapping)

```php
// Different role mapping strategies
interface RoleMappingStrategy {
    public function map(array $keycloakRoles): array;
}

class OneToOneMapping implements RoleMappingStrategy {
    public function map(array $keycloakRoles): array {
        // Simple one-to-one mapping
    }
}

class ComplexMapping implements RoleMappingStrategy {
    public function map(array $keycloakRoles): array {
        // Complex multi-role mapping
    }
}
```

### Observer Pattern (Events)

```php
// Fire events for extensibility
event(new KeycloakLoginSuccessful($user, $keycloakUser));

// Listeners observe events
class SyncKeycloakUser implements ShouldQueue {
    public function handle(KeycloakLoginSuccessful $event) {
        // Sync user data
    }
}
```

### Facade Pattern

```php
// Simplified interface to complex subsystem
class Keycloak {
    public static function login(): RedirectResponse {
        return app(KeycloakService::class)->redirect();
    }

    public static function logout(): RedirectResponse {
        return app(KeycloakAuthController::class)->logout();
    }
}
```

## Error Handling Architecture

```mermaid
flowchart TB
    Exception[Exception Thrown]
    --> Catch[ErrorHandler::handle]
    --> Sanitize[Sanitize Sensitive Data]
    --> Log[Write to Logs]
    --> UserMessage[Generate User Message]

    UserMessage --> CheckFallback{Fallback<br/>Enabled?}
    CheckFallback -->|Yes| LocalAuth[Redirect to Local Auth]
    CheckFallback -->|No| ErrorPage[Show Error Page]

    Catch --> Retry{Retriable<br/>Error?}
    Retry -->|Yes| RetryLogic[Exponential Backoff]
    Retry -->|No| UserMessage

    RetryLogic --> MaxRetries{Max Retries<br/>Reached?}
    MaxRetries -->|No| Catch
    MaxRetries -->|Yes| UserMessage
```

## Performance Considerations

### Caching Strategy

```mermaid
graph LR
    Request[User Request] --> CheckCache{Cache Hit?}
    CheckCache -->|Yes| ReturnCached[Return Cached Data]
    CheckCache -->|No| FetchKeycloak[Fetch from Keycloak]
    FetchKeycloak --> StoreCache[Store in Cache]
    StoreCache --> Return[Return Fresh Data]

    TTL[TTL: 5 minutes] -.-> StoreCache
```

### Database Optimization

- **Indexes**: Optimized for Keycloak lookups
- **Eager Loading**: Prevent N+1 queries
- **Transactions**: Ensure data consistency

## Extensibility Points

### Custom Event Listeners

```php
// Listen to authentication events
Event::listen(KeycloakLoginSuccessful::class, function ($event) {
    // Custom logic after successful login
});
```

### Custom Role Mapping

```php
// Override role mapping logic
$service = app(RoleMappingService::class);
$service->setRoleMapping([
    'custom-role' => 'Custom Krayin Role',
]);
```

### Middleware Customization

```php
// Add custom middleware to Keycloak routes
Route::middleware(['web', 'custom.middleware'])->group(function () {
    Route::get('/keycloak/login', [KeycloakAuthController::class, 'redirect']);
});
```

## Technology Stack

- **Framework**: Laravel 10+
- **PHP**: 8.2+
- **Database**: MySQL/PostgreSQL
- **Authentication**: OAuth 2.0 / OpenID Connect
- **Security**: Laravel Encryption, HTTPS
- **Cache**: Laravel Cache (Redis/Memcached)
- **Testing**: PHPUnit
- **Documentation**: Markdown, Mermaid diagrams

## Best Practices Implemented

1. **SOLID Principles**: Single responsibility, dependency injection
2. **12-Factor App**: Configuration via environment variables
3. **Security by Design**: Defense in depth, fail securely
4. **Clean Code**: PSR-12 standards, meaningful names
5. **Documentation**: Inline PHPDoc, architecture diagrams
6. **Testing**: Unit, feature, and integration tests
7. **Error Handling**: Graceful degradation, user-friendly messages
8. **Performance**: Caching, query optimization, lazy loading
