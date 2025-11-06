# GitHub Issue Management Guide

## Overview
This project uses GitHub Issues for comprehensive task and bug tracking. All work is organized into milestones, labeled appropriately, and tracked through to completion.

## Issue Tracking System

### Organization Structure
- **14 Milestones**: One for each phase of the implementation plan
- **Labels**: For categorization and priority
- **Issues**: Detailed tasks with acceptance criteria and checklists
- **Projects**: Optional Kanban boards for workflow visualization

## Working with Issues

### Before Starting Work

1. **Check Open Issues**
   ```bash
   # View issues in current milestone
   gh issue list --milestone "Phase 4: Keycloak Service"
   
   # View all open issues
   gh issue list --state open
   
   # View your assigned issues
   gh issue list --assignee @me
   ```

2. **Assign Yourself**
   ```bash
   gh issue edit <issue-number> --add-assignee @me
   ```

3. **Create Feature Branch**
   Link branch to issue with naming convention:
   ```bash
   git checkout -b feature/<issue-number>-<description>
   # Example: feature/4-keycloak-service
   ```

### During Development

1. **Reference Issue in Commits**
   ```bash
   git commit -m "feat: implement OAuth2 callback handler (#4)"
   git commit -m "test: add unit tests for token refresh (#4)"
   git commit -m "docs: update authentication flow documentation (#4)"
   ```

2. **Update Issue Progress**
   - Add comments with progress updates
   - Check off completed tasks in issue checklist
   - Link related PRs and issues

3. **Link Related Issues**
   ```bash
   # In issue comments or PR descriptions
   "Related to #12"
   "Depends on #8"
   "Blocks #15"
   ```

### When Completing Work

1. **Ensure All Tasks Completed**
   - Review issue checklist
   - Verify acceptance criteria met
   - Confirm tests pass

2. **Update Documentation**
   - Update CHANGELOG.md
   - Update Wiki if needed
   - Update .kennis docs

3. **Create Pull Request**
   ```bash
   gh pr create \
     --title "feat: Implement Keycloak Service (#4)" \
     --body "Closes #4
   
   ## Summary
   Implemented KeycloakService with full OAuth2/OIDC support.
   
   ## Changes
   - Added KeycloakService class
   - Implemented authorization flow
   - Added token management
   - Created comprehensive tests
   
   ## Testing
   - All unit tests passing
   - 85% code coverage
   - Manual testing completed
   
   ## Documentation
   - Updated CHANGELOG.md
   - Added service documentation to Wiki
   - Updated API reference" \
     --base develop
   ```

## Automatic Bug/Error Issue Creation

### When to Create Issues Automatically

Create issues automatically when encountering:
- ❌ **Build/Compilation Errors**: Any error during `composer install`, `npm build`, etc.
- ❌ **Test Failures**: Failed unit tests, feature tests, or integration tests
- ❌ **Runtime Errors**: Exceptions, errors, or unexpected behavior
- ❌ **Security Vulnerabilities**: Any security concern discovered
- ❌ **Performance Issues**: Slow queries, memory leaks, degradation
- ❌ **Integration Problems**: Issues with Keycloak API, Laravel, or Krayin CRM

### Bug Issue Template

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
- Keycloak Version: <version>
- Branch: <branch-name>
- Commit: <commit-hash>

## Related Issues
- Related to #<issue-number> (if applicable)
- Blocks #<issue-number> (if applicable)

## Severity
- [ ] Critical (blocks development/production)
- [ ] High (major functionality affected)
- [ ] Medium (feature impaired but workaround exists)
- [ ] Low (minor issue, cosmetic)

## Suggested Fix (if known)
<Your analysis or suggested solution>

## Additional Context
<Any additional information, screenshots, logs>" \
  --label "bug,priority-high" \
  --assignee @me
```

### Priority Labels for Bugs

- **priority-high**: Critical bugs blocking progress, security issues
  - Blocks development or production
  - Security vulnerabilities
  - Data loss or corruption
  - Complete feature failure

- **priority-medium**: Bugs affecting functionality with workarounds
  - Partial feature failure
  - Performance degradation
  - Usability issues
  - Has workaround available

- **priority-low**: Minor bugs, cosmetic issues
  - Cosmetic issues
  - Minor UI inconsistencies
  - Non-critical edge cases
  - Documentation errors

## Issue Creation Examples

### Example 1: Test Failure

```bash
gh issue create \
  --title "Bug: KeycloakService token refresh test failing" \
  --body "## Bug Description
The \`test_token_refresh_success()\` test is failing with a Guzzle timeout error.

## Steps to Reproduce
1. Run \`composer test\`
2. Test \`KeycloakServiceTest::test_token_refresh_success\` fails

## Expected Behavior
Test should pass with mocked Keycloak response completing in <1 second.

## Actual Behavior
GuzzleException: Connection timeout after 30 seconds

## Error Messages
\`\`\`
1) Tests\Unit\Services\KeycloakServiceTest::test_token_refresh_success
GuzzleHttp\Exception\ConnectException: cURL error 28: Connection timed out after 30000 milliseconds

/vendor/guzzlehttp/guzzle/src/Handler/CurlFactory.php:211
\`\`\`

## Environment
- PHP Version: 8.2.12
- Laravel Version: 10.x
- Krayin Version: 2.0
- Branch: feature/4-keycloak-service
- Commit: abc123def

## Severity
- [x] High (blocks testing)

## Suggested Fix
Issue appears to be with mock setup not intercepting HTTP call.
Check MockHandler configuration in test setup.

## Additional Context
Only this specific test fails. Other token-related tests pass." \
  --label "bug,testing,priority-high,phase-4" \
  --assignee @me
```

### Example 2: Runtime Error

```bash
gh issue create \
  --title "Bug: Undefined array key 'email' in UserProvisioningService" \
  --body "## Bug Description
Getting 'Undefined array key' error when Keycloak user has no email configured.

## Steps to Reproduce
1. Configure Keycloak user without email address
2. Authenticate with that user
3. Error thrown in UserProvisioningService::provisionUser()

## Expected Behavior
Should handle missing email gracefully, either:
- Use alternative identifier (username)
- Show user-friendly error message
- Skip user provisioning with proper logging

## Actual Behavior
Fatal error crashes authentication flow completely.

## Error Messages
\`\`\`
ErrorException: Undefined array key 'email'

src/Services/UserProvisioningService.php:45
    \$email = \$keycloakUser['email'];
\`\`\`

## Environment
- PHP Version: 8.2.12
- Laravel Version: 10.x
- Keycloak Version: 22.0.5
- Branch: develop
- Commit: def456abc

## Related Issues
- Related to #4 (User Provisioning)

## Severity
- [x] High (breaks authentication flow)

## Suggested Fix
Add null coalescing operator with fallback:
\`\`\`php
\$email = \$keycloakUser['email'] ?? \$keycloakUser['username'] ?? null;

if (!\$email) {
    throw new KeycloakUserProvisioningException(
        'User must have email or username configured'
    );
}
\`\`\`

## Additional Context
Keycloak allows users without email in certain configurations.
Need to handle this edge case properly." \
  --label "bug,security,priority-high,phase-6" \
  --assignee @me
```

### Example 3: Security Vulnerability

```bash
gh issue create \
  --title "Security: Potential XSS in user profile display" \
  --body "## Security Issue Description
User-provided Keycloak profile data displayed without HTML escaping in admin panel.

## Vulnerability Details
- **Type**: Cross-Site Scripting (XSS)
- **Location**: `src/Resources/views/admin/users.blade.php`
- **Vector**: User profile fields from Keycloak
- **Impact**: Stored XSS allowing admin session hijacking

## Steps to Reproduce
1. Set Keycloak user 'name' to: \`<script>alert('XSS')</script>\`
2. Authenticate with that user
3. Admin views user list
4. Script executes in admin context

## Expected Behavior
All user-provided data should be HTML-escaped before display.

## Actual Behavior
Raw HTML rendered, allowing script execution.

## Vulnerable Code
\`\`\`blade
<!-- Vulnerable -->
<td>{!! \$user->name !!}</td>

<!-- Should be -->
<td>{{ \$user->name }}</td>
\`\`\`

## Environment
- All versions: 1.0.0 - 1.2.3
- Affects: Admin panel only

## Severity
- [x] Critical (security vulnerability)

## Remediation
1. Replace all \`{!! !!}\` with \`{{ }}\` for user data
2. Implement Content Security Policy headers
3. Add automated XSS testing
4. Security audit all Blade templates

## CVE
Requesting CVE assignment if needed.

## Disclosure
- Private disclosure to maintainers
- Public disclosure after patch release" \
  --label "security,priority-high,critical" \
  --assignee @me
```

## Issue Labels Reference

### Phase Labels
- `phase-1` through `phase-14`: Link issues to implementation phases

### Type Labels
- `enhancement`: New features or improvements
- `bug`: Something isn't working
- `documentation`: Documentation improvements
- `testing`: Testing related issues
- `security`: Security related issues
- `performance`: Performance improvements
- `refactor`: Code refactoring

### Priority Labels
- `priority-high`: Critical, address immediately
- `priority-medium`: Important, address soon
- `priority-low`: Nice to have, can defer

### Status Labels
- `in-progress`: Currently being worked on
- `blocked`: Blocked by another issue
- `needs-review`: Needs review or feedback
- `wont-fix`: Won't be implemented
- `duplicate`: Duplicate of another issue

### Other Labels
- `good-first-issue`: Good for newcomers
- `help-wanted`: Extra attention needed
- `question`: Question or clarification needed

## Viewing Project Status

### Useful Commands

```bash
# Check current milestone progress
gh issue list --milestone "Phase 4: Keycloak Service"

# View all open issues
gh issue list --state open

# View issues by label
gh issue list --label "bug,priority-high"

# View your assigned issues
gh issue list --assignee @me

# View issues needing review
gh issue list --label "needs-review"

# View blocked issues
gh issue list --label "blocked"

# Create issue from template
gh issue create --template bug_report

# Close issue with comment
gh issue close <issue-number> -c "Fixed in #45"
```

### Dashboard Views

Create saved searches in GitHub:
- **My Issues**: `is:issue assignee:@me is:open`
- **High Priority**: `is:issue is:open label:priority-high`
- **Bugs**: `is:issue is:open label:bug`
- **Phase 4**: `is:issue is:open milestone:"Phase 4"`

## Best Practices

### Issue Creation
- ✅ Use descriptive, searchable titles
- ✅ Include all relevant context
- ✅ Add appropriate labels
- ✅ Link related issues
- ✅ Provide reproduction steps
- ❌ Don't create duplicate issues
- ❌ Don't be vague or generic

### Issue Management
- ✅ Keep issues focused on single concern
- ✅ Update status regularly
- ✅ Close resolved issues promptly
- ✅ Document resolution in closing comment
- ❌ Don't let issues go stale
- ❌ Don't reopen without new information

### Communication
- ✅ Be clear and professional
- ✅ Provide context in comments
- ✅ Tag relevant people
- ✅ Update progress regularly
- ❌ Don't spam mentions
- ❌ Don't be rude or dismissive

### Automation
- ✅ Auto-create bugs when encountered
- ✅ Link commits to issues
- ✅ Close issues via PR keywords
- ✅ Use issue templates
- ❌ Don't ignore automation failures

## Issue Templates

Create issue templates in `.github/ISSUE_TEMPLATE/`:

### Bug Report Template
```markdown
---
name: Bug Report
about: Report a bug or error
title: 'Bug: '
labels: bug
assignees: ''
---

## Bug Description
A clear description of the bug.

## Steps to Reproduce
1. First step
2. Second step
3. Error occurs

## Expected Behavior
What should happen.

## Actual Behavior
What actually happens.

## Error Messages
```
Paste error output here
```

## Environment
- PHP Version:
- Laravel Version:
- Krayin Version:
- Branch:

## Severity
- [ ] Critical
- [ ] High
- [ ] Medium
- [ ] Low
```

### Feature Request Template
```markdown
---
name: Feature Request
about: Suggest a new feature
title: 'Feature: '
labels: enhancement
assignees: ''
---

## Feature Description
Clear description of the proposed feature.

## Use Case
Why is this feature needed?

## Proposed Solution
How should this work?

## Alternatives Considered
Other approaches considered.

## Additional Context
Any other relevant information.
```

## Related Documentation

- [Git Workflow](GIT_WORKFLOW.md) - Branch management and conventions
- [CHANGELOG Management](CHANGELOG_MANAGEMENT.md) - Tracking changes
- Main [CLAUDE.md](../CLAUDE.md) - Complete development guidelines
