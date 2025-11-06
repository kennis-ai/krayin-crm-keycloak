# Claude Code Instructions - Krayin CRM Keycloak SSO Extension

## Documentation Access
- You can use the MCP Server deepwiki to get the latest documentation

## Internationalization (i18n)

### Supported Languages
All implementations must support multiple languages from the start:

- **English (en)**: Primary language, always required
- **Brazilian Portuguese (pt_BR)**: Secondary language, always required

### Implementation Guidelines

#### Translation Files
All user-facing text must have translations in both languages:

**Location**: `src/Resources/lang/{locale}/`

**Structure**:
```
src/Resources/lang/
├── en/
│   ├── keycloak.php       # English translations
│   ├── messages.php
│   └── validation.php
└── pt_BR/
    ├── keycloak.php       # Brazilian Portuguese translations
    ├── messages.php
    └── validation.php
```

#### What to Translate
- **Views**: All text in Blade templates
- **Flash Messages**: Success, error, info messages
- **Form Labels**: Input labels, placeholders, help text
- **Button Text**: Submit, cancel, action buttons
- **Validation Messages**: Custom validation error messages
- **Log Messages**: User-facing log entries
- **Email Templates**: All email content
- **Error Pages**: Error messages and descriptions

#### What NOT to Translate
- Configuration keys
- Database column names
- Class names and method names
- Code comments (keep in English)
- Technical log entries (internal debugging)
- API endpoints and route names

#### Translation Keys Convention
Use dot notation with descriptive keys:

```php
// Good
'auth.login_success' => 'Login successful'
'auth.login_failed' => 'Login failed'
'user.provisioned' => 'User account created successfully'

// Bad (avoid single words)
'success' => 'Success'
'error' => 'Error'
```

#### Usage in Code
Always use translation helpers:

```php
// In controllers
return redirect()->back()->with('success', __('keycloak::auth.login_success'));

// In views
<h1>{{ __('keycloak::auth.login_title') }}</h1>

// With parameters
__('keycloak::user.welcome', ['name' => $user->name])
```

#### Testing Translations
- Test both languages during development
- Ensure all keys exist in both language files
- Verify pluralization rules work correctly
- Check date/time formatting for each locale

### Quality Standards
- **Completeness**: Every English key must have a pt_BR translation
- **Accuracy**: Translations should be natural and idiomatic, not literal
- **Consistency**: Use consistent terminology across the package
- **Context**: Provide context in comments when translation might be ambiguous

## Git Workflow
- We want to work with the best practices of gitflow and we need to create specific branches for each type of implementation: feature, fix, etc. Always consider this before implementing something on codebase.

## Branch Naming Convention
- **Feature branches**: `feature/<feature-name>` (e.g., `feature/keycloak-authentication`)
- **Fix branches**: `fix/<issue-description>` (e.g., `fix/token-refresh-bug`)
- **Hotfix branches**: `hotfix/<critical-fix>` (e.g., `hotfix/security-patch`)
- **Release branches**: `release/<version>` (e.g., `release/1.0.0`)

## Main Branches
- **main**: Production-ready code
- **develop**: Integration branch for features

## Documentation Structure
- **Implementation plans**: Should be created at `.kennis` folder
- **User guides**: Should be created on Wiki as GitHub Wiki pages
- **Development guides**: Should be created on Wiki as GitHub Wiki pages
- **Wiki location**: `/Users/possebon/workspaces/kennis/krayin-crm/keycloak.wiki`

## Development Workflow
1. Always create a feature/fix branch from `develop`
2. Implement changes in the feature branch
3. Update documentation in `.kennis` for technical implementation details
4. Update Wiki for user-facing documentation
5. Create pull request to merge back to `develop`
6. After testing, merge `develop` to `main` for release

## Documentation Guidelines
- Technical architecture and implementation details → `.kennis/` folder
- API documentation and usage examples → Wiki
- User guides and installation instructions → Wiki
- Change logs and version history → Wiki

## Version Control
- Follow semantic versioning (MAJOR.MINOR.PATCH)
- Update version in `composer.json` and relevant config files
- Tag releases in git

## CHANGELOG.md Management

### Automatic Updates
The CHANGELOG.md file must be updated automatically with every significant change to the codebase. This provides a clear history of all modifications for users and developers.

### When to Update CHANGELOG
Update the CHANGELOG.md file when:
- **Completing a phase**: After finishing any implementation phase
- **Fixing bugs**: When resolving issues or bugs
- **Adding features**: When implementing new functionality
- **Making breaking changes**: When modifying APIs or behavior
- **Releasing versions**: When preparing for release
- **Security updates**: When fixing security vulnerabilities
- **Deprecations**: When marking features as deprecated

### CHANGELOG Format
Follow the [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) format with these sections:

#### Standard Sections
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

### Update Guidelines

#### Phase Completion Updates
When completing a phase, add entries like:

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

#### Bug Fix Updates
When fixing bugs, reference the issue number:

```markdown
### Fixed
- Fixed token refresh failing with timeout error (#15)
- Resolved undefined array key 'email' in user provisioning (#16)
- Corrected role mapping for administrator role (#17)
```

#### Feature Updates
When adding features:

```markdown
### Added
- Implemented automatic token refresh with grace period
- Added support for multi-realm configuration
- Created admin dashboard for Keycloak management
```

#### Breaking Changes
Mark breaking changes clearly:

```markdown
### Changed
- **BREAKING**: Modified KeycloakService constructor signature
  - Now requires RoleMappingService as second parameter
  - Update usage: `new KeycloakService($config, $roleMapper)`
- **BREAKING**: Renamed `getToken()` to `getAccessToken()`
```

### Version Release Process

#### 1. Before Release
Update CHANGELOG with release version and date:

```markdown
## [1.0.0] - 2025-11-25

### Added
- Full OAuth 2.0 / OpenID Connect integration with Keycloak
- Automatic user provisioning and synchronization
...
```

#### 2. Update Unreleased Section
Move items from `[Unreleased]` to the new version section.

#### 3. Create Git Tag
After updating CHANGELOG:

```bash
git add CHANGELOG.md
git commit -m "chore: release version 1.0.0"
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin v1.0.0
```

### Automation Template

Use this template when updating CHANGELOG after completing work:

```bash
# Determine the type of change
CHANGE_TYPE="Added"  # Added, Changed, Fixed, Security, etc.
VERSION="Unreleased"  # or specific version like "1.0.0"

# Update CHANGELOG.md
# Add entry under appropriate section in [Unreleased] or version section
```

### Example: Phase Completion Entry

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

### Example: Bug Fix Entry

```markdown
## [Unreleased]

### Fixed
- Fixed Keycloak token refresh failing with connection timeout (#15)
  - Increased HTTP timeout from 10s to 30s
  - Added retry logic with exponential backoff
  - Improved error messages for debugging
```

### Example: Security Update Entry

```markdown
## [Unreleased]

### Security
- Fixed potential XSS vulnerability in user data display (#23)
  - Added HTML escaping for all user-provided fields
  - Implemented Content Security Policy headers
  - Updated dependencies to patch known vulnerabilities
```

### Best Practices

1. **Be Specific**: Include clear descriptions of what changed
2. **Reference Issues**: Link to GitHub issues using (#issue-number)
3. **Group Related Changes**: Keep related changes together
4. **Use Present Tense**: "Add feature" not "Added feature" in descriptions
5. **Highlight Breaking Changes**: Use **BREAKING** prefix
6. **Keep Chronological Order**: Newest entries at the top
7. **Update Regularly**: Don't let CHANGELOG get out of sync
8. **Include Migration Notes**: For breaking changes, explain how to migrate

### Migration Guide Example

For breaking changes, add migration instructions:

```markdown
### Changed
- **BREAKING**: Modified configuration structure
  - `keycloak.client_id` moved to `keycloak.oauth.client_id`
  - `keycloak.client_secret` moved to `keycloak.oauth.client_secret`

  **Migration**: Update your `config/keycloak.php`:
  ```php
  // Old
  'client_id' => env('KEYCLOAK_CLIENT_ID'),

  // New
  'oauth' => [
      'client_id' => env('KEYCLOAK_CLIENT_ID'),
      'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
  ]
  ```
```

### Commit Message for CHANGELOG Updates

Use consistent commit messages:

```bash
# For phase completion
git commit -m "docs: update CHANGELOG for Phase 4 completion"

# For bug fixes
git commit -m "docs: update CHANGELOG with bug fix #15"

# For releases
git commit -m "chore: release version 1.0.0"
```

### Quality Checklist

Before committing CHANGELOG updates, verify:

- [ ] Entry is under correct section (Added, Fixed, etc.)
- [ ] Version is correct ([Unreleased] or specific version)
- [ ] Issue numbers are referenced where applicable
- [ ] Breaking changes are clearly marked
- [ ] Date format is YYYY-MM-DD for releases
- [ ] Entries are clear and descriptive
- [ ] Related changes are grouped together
- [ ] Format follows Keep a Changelog standard

## GitHub Issue Management

### Issue Tracking System
This project uses GitHub Issues for comprehensive task and bug tracking. All work is organized into:

- **14 Milestones**: One for each phase of the implementation plan
- **Labels**: For categorization (phase-1 through phase-14, enhancement, bug, documentation, testing, security, priority levels)
- **Issues**: Detailed tasks with acceptance criteria and checklists

### Working with Issues

#### Before Starting Work
1. Check open issues in the current milestone
2. Assign yourself to the issue you'll work on
3. Create a feature branch linked to the issue: `feature/<issue-number>-<description>`
   - Example: `feature/2-core-package-structure`

#### During Development
1. Reference the issue in commits: `git commit -m "feat: implement service provider (#2)"`
2. Update issue with progress comments
3. Check off completed tasks in the issue checklist
4. Link related issues if dependencies are discovered

#### When Completing Work
1. Ensure all tasks in the issue are completed
2. Update relevant documentation
3. Create PR with `Closes #<issue-number>` in description
4. Request review if needed

### Automatic Bug/Error Issue Creation

**IMPORTANT**: Whenever you encounter bugs, errors, or issues during development, automatically create a GitHub issue using the following workflow:

#### When to Create Issues Automatically
- **Build/Compilation Errors**: Any error during `composer install`, `npm build`, etc.
- **Test Failures**: Failed unit tests, feature tests, or integration tests
- **Runtime Errors**: Exceptions, errors, or unexpected behavior during execution
- **Security Vulnerabilities**: Any security concern discovered
- **Performance Issues**: Slow queries, memory leaks, or performance degradation
- **Integration Problems**: Issues with Keycloak API, Laravel, or Krayin CRM integration

#### Bug Issue Template
When creating bug issues, use this format:

```bash
gh issue create \
  --title "Bug: <concise description>" \
  --body "## Bug Description
<Clear description of the issue>

## Steps to Reproduce
1. Step one
2. Step two
3. Step three

## Expected Behavior
<What should happen>

## Actual Behavior
<What actually happens>

## Error Messages
\`\`\`
<paste error output>
\`\`\`

## Environment
- PHP Version: <version>
- Laravel Version: <version>
- Krayin Version: <version>
- Branch: <branch-name>

## Related Issues
- Related to #<issue-number> (if applicable)

## Severity
- [ ] Critical (blocks development)
- [ ] High (major functionality affected)
- [ ] Medium (feature impaired)
- [ ] Low (minor issue)

## Suggested Fix (if known)
<Your analysis or suggested solution>" \
  --label "bug,priority-high" \
  --assignee @me
```

#### Priority Labels for Bugs
- **priority-high**: Critical bugs blocking progress, security issues
- **priority-medium**: Bugs affecting functionality but with workarounds
- **priority-low**: Minor bugs, cosmetic issues

#### Examples

**Example 1: Test Failure**
```bash
gh issue create \
  --title "Bug: KeycloakService token refresh test failing" \
  --body "## Bug Description
The \`test_token_refresh_success()\` test is failing with a Guzzle timeout error.

## Steps to Reproduce
1. Run \`composer test\`
2. Test \`KeycloakServiceTest::test_token_refresh_success\` fails

## Expected Behavior
Test should pass with mocked Keycloak response

## Actual Behavior
GuzzleException: Connection timeout after 30 seconds

## Error Messages
\`\`\`
GuzzleHttp\\Exception\\ConnectException: cURL error 28: Connection timed out
\`\`\`

## Severity
- [x] High (blocks testing)

## Suggested Fix
Increase timeout or improve mock setup" \
  --label "bug,testing,priority-high,phase-4"
```

**Example 2: Runtime Error**
```bash
gh issue create \
  --title "Bug: Undefined array key 'email' in UserProvisioningService" \
  --body "## Bug Description
Getting 'Undefined array key' error when Keycloak user has no email.

## Steps to Reproduce
1. Authenticate with Keycloak user without email
2. Error thrown in UserProvisioningService::provisionUser()

## Expected Behavior
Should handle missing email gracefully

## Actual Behavior
Fatal error: Undefined array key 'email'

## Error Messages
\`\`\`
ErrorException: Undefined array key 'email'
at src/Services/UserProvisioningService.php:45
\`\`\`

## Severity
- [x] High (breaks authentication flow)

## Suggested Fix
Add null coalescing operator: \$email = \$keycloakUser['email'] ?? null;" \
  --label "bug,security,priority-high,phase-6"
```

### Issue Labels Reference

#### Phase Labels
- `phase-1` through `phase-14`: Link issues to specific implementation phases

#### Type Labels
- `enhancement`: New features or improvements
- `bug`: Something isn't working
- `documentation`: Documentation improvements
- `testing`: Testing related
- `security`: Security related

#### Priority Labels
- `priority-high`: Critical, must be addressed immediately
- `priority-medium`: Important, should be addressed soon
- `priority-low`: Nice to have, can be deferred

#### Other Labels
- `good-first-issue`: Good for newcomers
- `help-wanted`: Extra attention needed

### Viewing Project Status

#### Check Current Milestone Progress
```bash
gh issue list --milestone "Phase 2: Core Package Structure"
```

#### View All Open Issues
```bash
gh issue list --state open
```

#### View Issues by Label
```bash
gh issue list --label "bug,priority-high"
```

#### View Your Assigned Issues
```bash
gh issue list --assignee @me
```

### Best Practices
1. **One Issue = One Concern**: Keep issues focused on a single task or bug
2. **Descriptive Titles**: Use clear, searchable titles
3. **Reference Issues**: Link related issues and PRs
4. **Update Progress**: Comment on issues with progress updates
5. **Close Issues**: Close issues when work is completed and merged
6. **Auto-Create on Errors**: Always create issues when encountering bugs
