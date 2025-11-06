# Development Notes - Krayin CRM Keycloak SSO Extension

## Development Environment Setup

### Prerequisites

1. **Keycloak Server** (for testing)
   - Docker option: `docker run -p 8080:8080 -e KEYCLOAK_ADMIN=admin -e KEYCLOAK_ADMIN_PASSWORD=admin quay.io/keycloak/keycloak:latest start-dev`
   - Access at: http://localhost:8080

2. **Krayin CRM Instance**
   - Version 2.0 or higher
   - Running locally or in development environment

3. **Development Tools**
   - PHP >= 8.2
   - Composer >= 2.5
   - Git
   - IDE with PHP support

### Local Development Setup

1. Clone the repository:
```bash
git clone <repository-url>
cd keycloak
```

2. Install dependencies (when available):
```bash
composer install
```

3. Set up environment variables:
```bash
cp .env.example .env
# Edit .env with your Keycloak settings
```

---

## Keycloak Test Configuration

### Setting Up Test Realm

1. Log in to Keycloak admin console
2. Create a new realm: `krayin-test`
3. Create a client:
   - Client ID: `krayin-crm-local`
   - Client Protocol: `openid-connect`
   - Access Type: `confidential`
   - Standard Flow Enabled: `ON`
   - Valid Redirect URIs: `http://localhost:8000/admin/auth/keycloak/callback`

4. Create test users:
   - admin@test.com (with admin role)
   - manager@test.com (with manager role)
   - user@test.com (with user role)

### Test Roles

Create these roles in Keycloak:
- `keycloak_admin`
- `keycloak_manager`
- `keycloak_user`

---

## Git Workflow

### Branch Strategy (Gitflow)

```
main
  └── develop
       ├── feature/package-structure
       ├── feature/keycloak-service
       ├── feature/auth-controller
       └── ...
```

### Creating a Feature Branch

```bash
# Always branch from develop
git checkout develop
git pull origin develop

# Create feature branch
git checkout -b feature/my-feature

# Work on feature...

# Commit changes
git add .
git commit -m "feat: add my feature"

# Push to remote
git push origin feature/my-feature

# Create PR to develop
```

### Commit Message Convention

Follow Conventional Commits:

- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation changes
- `style:` - Code style changes (formatting, etc.)
- `refactor:` - Code refactoring
- `test:` - Adding or updating tests
- `chore:` - Maintenance tasks

**Examples**:
```bash
git commit -m "feat: implement KeycloakService with OAuth flow"
git commit -m "fix: resolve token refresh race condition"
git commit -m "docs: update API reference with examples"
git commit -m "test: add unit tests for UserProvisioningService"
```

---

## Coding Standards

### PSR-12

This project follows PSR-12 coding standards.

**Check code style**:
```bash
composer check-style
```

**Fix code style automatically**:
```bash
composer fix-style
```

### Type Hints

Always use strict types and type hints:

```php
<?php

declare(strict_types=1);

namespace Webkul\KeycloakSSO\Services;

class KeycloakService
{
    public function getAuthorizationUrl(): string
    {
        // Implementation
    }

    public function handleCallback(Request $request): array
    {
        // Implementation
    }
}
```

### Documentation

Use PHPDoc for all public methods:

```php
/**
 * Retrieves user information from Keycloak.
 *
 * @param string $accessToken Valid Keycloak access token
 * @return array User information including email, name, roles
 * @throws KeycloakConnectionException If API call fails
 */
public function getUserInfo(string $accessToken): array
{
    // Implementation
}
```

---

## Testing Strategy

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit tests/Unit
./vendor/bin/phpunit tests/Feature

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage
```

### Writing Tests

#### Unit Test Example

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Webkul\KeycloakSSO\Services\KeycloakService;

class KeycloakServiceTest extends TestCase
{
    /** @test */
    public function it_generates_authorization_url()
    {
        $service = new KeycloakService();
        $url = $service->getAuthorizationUrl();

        $this->assertStringContainsString('openid-connect/auth', $url);
        $this->assertStringContainsString('client_id=', $url);
    }
}
```

#### Feature Test Example

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Webkul\User\Models\User;

class KeycloakAuthenticationTest extends TestCase
{
    /** @test */
    public function user_can_login_with_keycloak()
    {
        $response = $this->get(route('admin.keycloak.login'));

        $response->assertRedirect();
        $response->assertRedirectContains('keycloak');
    }
}
```

### Mock Keycloak Responses

```php
use Illuminate\Support\Facades\Http;

Http::fake([
    'keycloak.example.com/token' => Http::response([
        'access_token' => 'test_token',
        'refresh_token' => 'test_refresh',
        'expires_in' => 300,
    ], 200),

    'keycloak.example.com/userinfo' => Http::response([
        'sub' => 'test-uuid',
        'email' => 'test@example.com',
        'name' => 'Test User',
    ], 200),
]);
```

---

## Debugging

### Enable Debug Mode

In `.env`:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Logging

The extension logs to separate channel:

```php
// config/logging.php
'channels' => [
    'keycloak' => [
        'driver' => 'daily',
        'path' => storage_path('logs/keycloak.log'),
        'level' => 'debug',
    ],
],
```

**Usage**:
```php
Log::channel('keycloak')->info('User authenticated', [
    'user_id' => $user->id,
    'keycloak_id' => $keycloakId,
]);
```

### Common Issues

#### Issue: "Call to undefined method"

**Cause**: Service not registered
**Fix**: Check service provider registration in `composer.json`

#### Issue: "Token validation failed"

**Cause**: Keycloak server unreachable or misconfigured
**Fix**:
1. Check `KEYCLOAK_BASE_URL` in `.env`
2. Verify Keycloak server is running
3. Check network connectivity

#### Issue: "User provisioning failed"

**Cause**: Database constraint violation or missing data
**Fix**:
1. Check migration ran successfully
2. Verify user data from Keycloak is complete
3. Check logs for specific error

---

## Performance Optimization

### Token Caching

Cache access tokens to reduce API calls:

```php
Cache::remember("keycloak_token_{$userId}", $expiresIn, function() {
    return $this->keycloakService->getAccessToken();
});
```

### Database Indexing

Ensure proper indexes exist:

```php
Schema::table('users', function (Blueprint $table) {
    $table->index('keycloak_id');
    $table->index('auth_provider');
});
```

### Query Optimization

Use eager loading to prevent N+1 queries:

```php
$user = User::with('role', 'groups')
    ->where('keycloak_id', $keycloakId)
    ->first();
```

---

## Security Considerations

### Token Storage

- Access tokens: Encrypted session
- Refresh tokens: Encrypted database column
- Never expose tokens in logs or responses

### CSRF Protection

Always maintain Laravel CSRF protection:

```php
// In forms
@csrf

// In AJAX
headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
}
```

### Input Validation

Always validate external data:

```php
$validated = $request->validate([
    'code' => 'required|string',
    'state' => 'required|string',
]);
```

---

## Package Development Workflow

### Phase 1: Setup (Current)
- ✅ Git repository initialized
- ✅ Documentation structure created
- ✅ Gitflow branches set up
- ⏳ Composer package configuration

### Phase 2: Core Development
1. Create package structure
2. Implement services
3. Add controllers
4. Create middleware
5. Set up events

### Phase 3: Integration
1. Test with Krayin CRM
2. UI integration
3. Configuration interface

### Phase 4: Testing
1. Unit tests
2. Feature tests
3. Integration tests
4. Manual QA

### Phase 5: Documentation
1. Code documentation
2. User guides (Wiki)
3. API reference
4. Video tutorials (optional)

### Phase 6: Release
1. Version tagging
2. Package publication
3. Announcement

---

## Useful Commands

### Composer

```bash
# Install dependencies
composer install

# Update dependencies
composer update

# Dump autoload
composer dump-autoload

# Run tests
composer test

# Check code style
composer check-style

# Fix code style
composer fix-style
```

### Git

```bash
# Check status
git status

# Create feature branch
git checkout -b feature/my-feature

# Commit changes
git add .
git commit -m "feat: description"

# Push to remote
git push origin feature/my-feature

# Merge develop into current branch
git merge develop

# Switch branches
git checkout develop
git checkout main
```

### Artisan (when integrated with Krayin)

```bash
# Publish package assets
php artisan vendor:publish --provider="Webkul\KeycloakSSO\Providers\KeycloakSSOServiceProvider"

# Run migrations
php artisan migrate

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Run tests
php artisan test
```

---

## Resources

### External Documentation

- [Keycloak Documentation](https://www.keycloak.org/documentation)
- [OAuth 2.0 Specification](https://oauth.net/2/)
- [OpenID Connect Specification](https://openid.net/connect/)
- [Laravel Documentation](https://laravel.com/docs)
- [Krayin CRM Docs](https://devdocs.krayincrm.com)

### Tools

- [Postman](https://www.postman.com/) - API testing
- [PHPStan](https://phpstan.org/) - Static analysis
- [PHP CS Fixer](https://cs.symfony.com/) - Code formatting
- [Xdebug](https://xdebug.org/) - Debugging

---

## Team Communication

### Before Starting Work

1. Check implementation plan
2. Read relevant architecture docs
3. Create feature branch
4. Update this notes file if needed

### During Development

1. Commit frequently
2. Write tests as you go
3. Update documentation
4. Log important decisions

### After Completion

1. Run full test suite
2. Check code style
3. Update CHANGELOG.md
4. Create PR with detailed description

---

## Decision Log

### 2025-01-06: Initial Architecture Decisions

**Decision**: Use Laravel Socialite for OAuth implementation
**Rationale**: Well-tested, community-supported, easy to integrate
**Alternatives Considered**: Custom OAuth implementation, other packages
**Status**: Approved

**Decision**: Store refresh tokens encrypted in database
**Rationale**: Security best practice, enables token refresh across sessions
**Alternatives Considered**: Session-only storage
**Status**: Approved

**Decision**: Maintain backward compatibility with local auth
**Rationale**: Safety net for Keycloak downtime, easier gradual rollout
**Alternatives Considered**: Keycloak-only authentication
**Status**: Approved

---

## Next Steps

1. ✅ Complete project setup (DONE)
2. ⏳ Create composer.json with package definition
3. ⏳ Set up basic package structure
4. ⏳ Implement KeycloakService
5. ⏳ Create authentication controller
6. ⏳ Add database migrations

---

**Document Version**: 1.0
**Last Updated**: 2025-01-06
**Maintainer**: Development Team
