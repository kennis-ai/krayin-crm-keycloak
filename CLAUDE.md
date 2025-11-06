# Claude Code Instructions - Krayin CRM Keycloak SSO Extension

## Documentation Access
- You can use the MCP Server deepwiki to get the latest documentation

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
