# CHANGELOG.md Management Guide

## Overview
The CHANGELOG.md file must be updated automatically with every significant change to the codebase. This provides a clear history of all modifications for users and developers.

This guide follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) format.

## When to Update CHANGELOG

Update the CHANGELOG.md file when:
- ✅ **Completing a phase**: After finishing any implementation phase
- ✅ **Fixing bugs**: When resolving issues or bugs
- ✅ **Adding features**: When implementing new functionality
- ✅ **Making breaking changes**: When modifying APIs or behavior
- ✅ **Releasing versions**: When preparing for release
- ✅ **Security updates**: When fixing security vulnerabilities
- ✅ **Deprecations**: When marking features as deprecated

## CHANGELOG Format

### Standard Sections

#### Section Types
- **Added**: New features
- **Changed**: Changes to existing functionality
- **Deprecated**: Soon-to-be removed features
- **Removed**: Removed features
- **Fixed**: Bug fixes
- **Security**: Security vulnerability fixes

#### Version Structure
```markdown
## [Version] - YYYY-MM-DD

### Added
- New feature description
- Another new feature

### Changed
- Modified behavior description

### Fixed
- Bug fix description (#issue-number)

### Security
- Security fix description
```

## Update Guidelines

### Phase Completion Updates

When completing a phase, add comprehensive entries:

```markdown
## [Unreleased]

### Added
- **Phase 3**: Database schema for Keycloak integration
  - Added `keycloak_id` column to users table
  - Added `auth_provider` enum column
  - Added `keycloak_refresh_token` encrypted column
  - Added `keycloak_token_expires_at` timestamp column
  - Created `HasKeycloakAuthentication` trait with 15+ helper methods
  - Added performance indexes for Keycloak lookups
```

### Bug Fix Updates

When fixing bugs, reference the issue number:

```markdown
### Fixed
- Fixed token refresh failing with timeout error (#15)
- Resolved undefined array key 'email' in user provisioning (#16)
- Corrected role mapping for administrator role (#17)
```

### Feature Updates

When adding features:

```markdown
### Added
- Implemented automatic token refresh with grace period
- Added support for multi-realm configuration
- Created admin dashboard for Keycloak management
```

### Breaking Changes

Mark breaking changes clearly with **BREAKING** prefix:

```markdown
### Changed
- **BREAKING**: Modified KeycloakService constructor signature
  - Now requires RoleMappingService as second parameter
  - Update usage: `new KeycloakService($config, $roleMapper)`
- **BREAKING**: Renamed `getToken()` to `getAccessToken()`
```

## Version Release Process

### 1. Before Release

Update CHANGELOG with release version and date:

```markdown
## [1.0.0] - 2025-11-25

### Added
- Full OAuth 2.0 / OpenID Connect integration with Keycloak
- Automatic user provisioning and synchronization
- Role mapping from Keycloak to Krayin CRM
- Token refresh mechanism with grace period
- Admin configuration interface
...
```

### 2. Update Unreleased Section

Move items from `[Unreleased]` to the new version section.

Create new empty `[Unreleased]` section at the top:

```markdown
## [Unreleased]

### Added

### Changed

### Fixed

### Security

## [1.0.0] - 2025-11-25
...
```

### 3. Create Git Tag

After updating CHANGELOG:

```bash
git add CHANGELOG.md
git commit -m "chore: release version 1.0.0"
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin v1.0.0
git push origin main
```

## Update Templates

### Phase Completion Template

```markdown
## [Unreleased]

### Added
- **Phase X**: <Phase Title> (#<issue-number>)
  - <Feature/component implemented>
  - <Another feature/component>
  - <Implementation detail>
  - <Test coverage information>
  - <Documentation updates>
```

### Bug Fix Template

```markdown
## [Unreleased]

### Fixed
- Fixed <description of problem> (#<issue-number>)
  - <What was causing the issue>
  - <How it was resolved>
  - <Any migration steps if needed>
```

### Security Update Template

```markdown
## [Unreleased]

### Security
- Fixed <security vulnerability description> (#<issue-number>)
  - <Severity level>
  - <Attack vector if applicable>
  - <Mitigation implemented>
  - <Affected versions>
  - <Upgrade instructions>
```

### Feature Addition Template

```markdown
## [Unreleased]

### Added
- <Feature name and description>
  - <Key capability 1>
  - <Key capability 2>
  - <Configuration requirements>
  - <Usage example or reference>
```

### Breaking Change Template

```markdown
## [Unreleased]

### Changed
- **BREAKING**: <What changed>
  - <Old behavior>
  - <New behavior>
  - <Migration steps>
  
  **Migration Example:**
  ```php
  // Old way
  <old code example>
  
  // New way
  <new code example>
  ```
```

## Detailed Examples

### Example 1: Phase Completion Entry

After completing Phase 4 (Keycloak Service), add:

```markdown
## [Unreleased]

### Added
- **Phase 4**: Keycloak Service Integration (#4)
  - Implemented `KeycloakService` with OAuth2/OpenID Connect flows
  - Added `getAuthorizationUrl()` for login redirection
  - Added `handleCallback()` for OAuth callback processing
  - Added `getUserInfo()` for user data retrieval
  - Added `refreshToken()` for automatic token renewal
  - Added `logout()` for Single Logout (SLO)
  - Added `validateToken()` for token validation
  - Added `getUserRoles()` for role retrieval
  - Implemented comprehensive error handling and logging
  - Created unit tests with 85% code coverage
```

### Example 2: Bug Fix Entry

```markdown
## [Unreleased]

### Fixed
- Fixed Keycloak token refresh failing with connection timeout (#15)
  - Increased HTTP timeout from 10s to 30s
  - Added retry logic with exponential backoff
  - Improved error messages for debugging
  - Added test coverage for timeout scenarios
```

### Example 3: Security Update Entry

```markdown
## [Unreleased]

### Security
- Fixed potential XSS vulnerability in user data display (#23)
  - Added HTML escaping for all user-provided fields
  - Implemented Content Security Policy headers
  - Updated dependencies to patch known vulnerabilities
  - Affects versions: 1.0.0 - 1.2.3
  - **Action Required**: Upgrade to version 1.2.4 immediately
```

### Example 4: Breaking Change with Migration

```markdown
## [Unreleased]

### Changed
- **BREAKING**: Modified configuration structure for better organization
  - Moved client credentials under `oauth` namespace
  - Restructured realm configuration
  
  **Migration Steps:**
  
  1. Update `config/keycloak.php`:
  ```php
  // Old structure
  'client_id' => env('KEYCLOAK_CLIENT_ID'),
  'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
  'realm' => env('KEYCLOAK_REALM'),
  
  // New structure
  'oauth' => [
      'client_id' => env('KEYCLOAK_CLIENT_ID'),
      'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
  ],
  'realm' => [
      'name' => env('KEYCLOAK_REALM'),
      'public_key' => env('KEYCLOAK_REALM_PUBLIC_KEY'),
  ],
  ```
  
  2. Update environment variables (optional - old names still work):
  ```bash
  # No changes required for backward compatibility
  # But recommended for clarity:
  KEYCLOAK_CLIENT_ID → KEYCLOAK_OAUTH_CLIENT_ID
  KEYCLOAK_CLIENT_SECRET → KEYCLOAK_OAUTH_CLIENT_SECRET
  ```
  
  3. Clear configuration cache:
  ```bash
  php artisan config:clear
  php artisan config:cache
  ```
```

## Best Practices

### Specificity
- ✅ Be specific about what changed
- ✅ Include context and rationale
- ✅ Mention affected components
- ❌ Don't use vague descriptions like "improved things"

### Issue References
- ✅ Link to GitHub issues using (#issue-number)
- ✅ Link to pull requests when relevant
- ✅ Reference related changes
- ❌ Don't forget to link tracking issues

### Grouping
- ✅ Group related changes together
- ✅ Use sub-bullets for details
- ✅ Keep logical organization
- ❌ Don't scatter related changes across sections

### Language
- ✅ Use present tense: "Add feature" not "Added feature"
- ✅ Be concise but descriptive
- ✅ Use imperative mood: "Fix bug" not "Fixes bug"
- ❌ Don't use passive voice

### Breaking Changes
- ✅ Always prefix with **BREAKING**
- ✅ Explain the change clearly
- ✅ Provide migration instructions
- ✅ Show before/after examples
- ❌ Don't hide breaking changes in other sections

### Chronological Order
- ✅ Newest entries at the top
- ✅ Latest version first
- ✅ [Unreleased] section always at top
- ❌ Don't add to bottom of sections

### Regular Updates
- ✅ Update CHANGELOG with each PR
- ✅ Don't let it get out of sync
- ✅ Review before releases
- ❌ Don't batch updates before release

## Commit Messages for CHANGELOG Updates

Use consistent commit messages:

```bash
# For phase completion
git commit -m "docs: update CHANGELOG for Phase 4 completion"

# For bug fixes
git commit -m "docs: update CHANGELOG with bug fix #15"

# For releases
git commit -m "chore: release version 1.0.0"

# For multiple updates
git commit -m "docs: update CHANGELOG with recent changes"
```

## Quality Checklist

Before committing CHANGELOG updates, verify:

- [ ] Entry is under correct section (Added, Fixed, etc.)
- [ ] Version is correct ([Unreleased] or specific version)
- [ ] Issue numbers are referenced where applicable
- [ ] Breaking changes are clearly marked with **BREAKING**
- [ ] Date format is YYYY-MM-DD for releases
- [ ] Entries are clear and descriptive
- [ ] Related changes are grouped together
- [ ] Format follows Keep a Changelog standard
- [ ] Migration instructions provided for breaking changes
- [ ] Technical details are accurate
- [ ] User impact is explained

## Automation Tips

### Git Hooks

Create a pre-commit hook to remind about CHANGELOG updates:

```bash
#!/bin/bash
# .git/hooks/pre-commit

# Check if source files changed but CHANGELOG not updated
if git diff --cached --name-only | grep -q "^src/"; then
    if ! git diff --cached --name-only | grep -q "CHANGELOG.md"; then
        echo "⚠️  Warning: Source files changed but CHANGELOG.md not updated"
        echo "Consider updating CHANGELOG.md if this is a significant change"
        # Uncomment to enforce:
        # exit 1
    fi
fi
```

### GitHub Actions

Consider adding a GitHub Action to validate CHANGELOG updates:

```yaml
name: Validate CHANGELOG

on:
  pull_request:
    paths:
      - 'src/**'

jobs:
  check-changelog:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Check CHANGELOG updated
        run: |
          if ! git diff origin/develop...HEAD --name-only | grep -q "CHANGELOG.md"; then
            echo "::warning::CHANGELOG.md not updated in this PR"
          fi
```

## Related Documentation

- [Git Workflow](GIT_WORKFLOW.md) - Branch management and commit conventions
- [GitHub Issue Management](../CLAUDE.md#github-issue-management) - Issue tracking
- Main [CLAUDE.md](../CLAUDE.md) - Full development guidelines
