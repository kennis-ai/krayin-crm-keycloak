# Contributing to Keycloak SSO Extension

Thank you for considering contributing to the Keycloak SSO Extension for Krayin CRM! This document provides guidelines and instructions for contributing.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How to Contribute](#how-to-contribute)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Testing Guidelines](#testing-guidelines)
- [Pull Request Process](#pull-request-process)
- [Issue Reporting](#issue-reporting)

## Code of Conduct

### Our Pledge

We are committed to providing a welcoming and inspiring community for all. Please be respectful and constructive in your interactions.

### Expected Behavior

- Use welcoming and inclusive language
- Be respectful of differing viewpoints
- Accept constructive criticism gracefully
- Focus on what is best for the community
- Show empathy towards community members

### Unacceptable Behavior

- Harassment or discriminatory language
- Trolling or insulting comments
- Public or private harassment
- Publishing others' private information
- Other unethical or unprofessional conduct

## How to Contribute

### Types of Contributions

We welcome many types of contributions:

1. **Bug Reports**: Report issues you encounter
2. **Feature Requests**: Suggest new features
3. **Code Contributions**: Fix bugs or implement features
4. **Documentation**: Improve or expand documentation
5. **Testing**: Write or improve tests
6. **Translations**: Add or improve translations

### First Time Contributors

Look for issues tagged with `good-first-issue` or `help-wanted`. These are great starting points!

## Development Setup

### Prerequisites

- PHP >= 8.2
- Composer >= 2.5
- Krayin CRM >= 2.0
- Git
- A Keycloak server for testing

### Fork and Clone

```bash
# Fork the repository on GitHub
# Clone your fork
git clone https://github.com/YOUR-USERNAME/krayin-crm-keycloak.git
cd krayin-crm-keycloak

# Add upstream remote
git remote add upstream https://github.com/kennis-ai/krayin-crm-keycloak.git
```

### Install Dependencies

```bash
composer install
```

### Configure Test Environment

```bash
# Copy phpunit configuration
cp phpunit.xml.dist phpunit.xml

# Configure test environment variables
# Edit phpunit.xml to set your test Keycloak server details
```

### Create Feature Branch

```bash
# Update your local develop branch
git checkout develop
git pull upstream develop

# Create feature branch
git checkout -b feature/your-feature-name
```

## Coding Standards

### PHP Code Style

We follow **PSR-12** coding standards.

#### Run Code Style Checks

```bash
# Check code style
composer check-style

# Automatically fix code style
composer fix-style
```

### Code Quality Rules

1. **Meaningful Names**: Use descriptive variable and method names
2. **Single Responsibility**: Each class/method should have one purpose
3. **DRY Principle**: Don't repeat yourself
4. **SOLID Principles**: Follow object-oriented best practices
5. **Type Declarations**: Use strict types and return type declarations

#### Example: Good Code

```php
<?php

declare(strict_types=1);

namespace Webkul\KeycloakSSO\Services;

class UserProvisioningService
{
    public function __construct(
        private RoleMappingService $roleMappingService,
        private KeycloakService $keycloakService
    ) {
    }

    public function findOrCreateUser(array $keycloakUser): User
    {
        // Clear, single-purpose method
        return $this->findExistingUser($keycloakUser)
            ?? $this->createNewUser($keycloakUser);
    }
}
```

### Documentation Standards

#### PHPDoc Comments

```php
/**
 * Find or create a user from Keycloak data.
 *
 * This method searches for an existing user by Keycloak ID or email.
 * If no user is found and auto-provisioning is enabled, a new user
 * is created automatically.
 *
 * @param  array  $keycloakUser  Keycloak user claims
 * @return User  The existing or newly created user
 *
 * @throws KeycloakUserProvisioningException  If provisioning fails
 */
public function findOrCreateUser(array $keycloakUser): User
{
    // Implementation
}
```

### Internationalization (i18n)

All user-facing text must support multiple languages:

#### English and Brazilian Portuguese Required

```php
// resources/lang/en/keycloak.php
'login_success' => 'Successfully logged in with Keycloak',

// resources/lang/pt_BR/keycloak.php
'login_success' => 'Login realizado com sucesso via Keycloak',
```

#### Usage in Code

```php
// Use translation helpers
return redirect()->with('success', __('keycloak::auth.login_success'));

// With parameters
__('keycloak::user.welcome', ['name' => $user->name])
```

## Testing Guidelines

### Test Coverage Requirements

- **New Features**: Must include tests
- **Bug Fixes**: Must include regression tests
- **Minimum Coverage**: 80% for new code

### Writing Tests

#### Unit Tests

```php
namespace Webkul\KeycloakSSO\Tests\Unit\Services;

use Webkul\KeycloakSSO\Tests\TestCase;

class KeycloakServiceTest extends TestCase
{
    public function test_service_validates_configuration(): void
    {
        $this->expectException(KeycloakConfigurationException::class);

        $config = [];
        new KeycloakService($config);
    }
}
```

#### Feature Tests

```php
namespace Webkul\KeycloakSSO\Tests\Feature;

class KeycloakAuthControllerTest extends TestCase
{
    public function test_redirect_to_keycloak_login(): void
    {
        $response = $this->get(route('keycloak.login'));

        $response->assertRedirect();
        $this->assertStringContainsString('keycloak', $response->headers->get('Location'));
    }
}
```

### Running Tests

```bash
# Run all tests
composer test

# Run specific test suite
./vendor/bin/phpunit tests/Unit
./vendor/bin/phpunit tests/Feature

# Run with coverage
composer test-coverage

# Run specific test
./vendor/bin/phpunit tests/Unit/Services/KeycloakServiceTest.php
```

## Pull Request Process

### Before Submitting

1. **Update from upstream**
```bash
git checkout develop
git pull upstream develop
git checkout your-feature-branch
git rebase develop
```

2. **Run tests**
```bash
composer test
composer check-style
```

3. **Update documentation** if needed

4. **Update CHANGELOG.md** in the `[Unreleased]` section

### Commit Messages

Follow the **Conventional Commits** specification:

```
<type>(<scope>): <subject>

<body>

<footer>
```

#### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

#### Examples

```bash
feat(auth): add support for multi-realm configuration

Implement support for multiple Keycloak realms by adding
realm selection to the configuration.

Closes #123

---

fix(tokens): resolve token refresh race condition

Fixed an issue where concurrent requests could cause
multiple token refresh attempts.

Fixes #456

---

docs(readme): update installation instructions

Added troubleshooting section and clarified Keycloak
server setup steps.
```

### Creating Pull Request

1. **Push to your fork**
```bash
git push origin your-feature-branch
```

2. **Create PR on GitHub**
   - Go to the main repository
   - Click "New Pull Request"
   - Select your branch
   - Fill in the PR template

3. **PR Title Format**
```
feat(scope): brief description
```

4. **PR Description Should Include**
   - Summary of changes
   - Motivation and context
   - Related issue numbers
   - Screenshots (if UI changes)
   - Testing instructions
   - Checklist completion

### PR Template

```markdown
## Summary
Brief description of what this PR does.

## Motivation
Why is this change necessary?

## Changes
- Change 1
- Change 2
- Change 3

## Related Issues
Closes #123
Fixes #456

## Testing
How to test these changes:
1. Step 1
2. Step 2
3. Step 3

## Screenshots (if applicable)
[Add screenshots here]

## Checklist
- [ ] Tests pass locally
- [ ] Code follows PSR-12 standards
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
- [ ] Translations added/updated
- [ ] No breaking changes (or documented)
```

### Review Process

1. **Automated Checks**: CI/CD pipeline runs tests
2. **Code Review**: Maintainers review your code
3. **Feedback**: Address review comments
4. **Approval**: At least one maintainer approval required
5. **Merge**: Maintainer merges your PR

## Issue Reporting

### Before Creating an Issue

1. **Search existing issues** to avoid duplicates
2. **Check documentation** for solutions
3. **Try latest version** to see if it's already fixed

### Bug Reports

Use the bug report template:

```markdown
**Describe the bug**
A clear and concise description.

**To Reproduce**
Steps to reproduce:
1. Go to '...'
2. Click on '....'
3. See error

**Expected behavior**
What you expected to happen.

**Screenshots**
If applicable.

**Environment:**
 - PHP Version: [e.g. 8.2]
 - Laravel Version: [e.g. 10.0]
 - Krayin Version: [e.g. 2.0]
 - Keycloak Version: [e.g. 20.0]

**Additional context**
Any other relevant information.
```

### Feature Requests

Use the feature request template:

```markdown
**Is your feature request related to a problem?**
Description of the problem.

**Describe the solution you'd like**
Clear description of what you want to happen.

**Describe alternatives you've considered**
Other solutions you've thought about.

**Additional context**
Any other context or screenshots.
```

## Development Workflow

### Branch Naming Convention

- `feature/<description>` - New features
- `fix/<description>` - Bug fixes
- `docs/<description>` - Documentation
- `test/<description>` - Test improvements
- `refactor/<description>` - Code refactoring

Examples:
- `feature/multi-realm-support`
- `fix/token-refresh-race-condition`
- `docs/installation-guide`

### Commit Workflow

```bash
# Make your changes
vim src/Services/KeycloakService.php

# Stage changes
git add src/Services/KeycloakService.php

# Commit with descriptive message
git commit -m "feat(auth): add multi-realm support"

# Push to your fork
git push origin feature/multi-realm-support
```

### Keeping Your Fork Updated

```bash
# Fetch upstream changes
git fetch upstream

# Update your develop branch
git checkout develop
git merge upstream/develop

# Rebase your feature branch
git checkout feature/your-feature
git rebase develop
```

## Getting Help

- **Documentation**: Check the [Wiki](https://github.com/kennis-ai/krayin-crm-keycloak/wiki)
- **Discussions**: Use [GitHub Discussions](https://github.com/kennis-ai/krayin-crm-keycloak/discussions)
- **Email**: suporte@kennis.com.br

## Recognition

Contributors will be recognized in:
- README.md credits section
- Release notes
- GitHub contributors page

Thank you for contributing! 🎉
