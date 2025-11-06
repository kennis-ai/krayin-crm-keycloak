# Git Workflow and Documentation Guidelines

## Overview
This project follows GitFlow best practices with strict branch naming conventions and documentation requirements.

## Git Workflow (GitFlow)

### Main Branches
- **main**: Production-ready code only
- **develop**: Integration branch for features

### Supporting Branches
- **Feature branches**: New features or enhancements
- **Fix branches**: Bug fixes
- **Hotfix branches**: Critical production fixes
- **Release branches**: Release preparation

## Branch Naming Convention

### Feature Branches
Format: `feature/<issue-number>-<feature-name>`

Examples:
- `feature/4-keycloak-service`
- `feature/12-comprehensive-test-suite`
- `feature/authentication-flow`

### Fix Branches
Format: `fix/<issue-number>-<issue-description>`

Examples:
- `fix/15-token-refresh-bug`
- `fix/23-missing-email-validation`
- `fix/user-provisioning-error`

### Hotfix Branches
Format: `hotfix/<critical-fix>`

Examples:
- `hotfix/security-patch`
- `hotfix/auth-bypass-vulnerability`
- `hotfix/production-crash`

### Release Branches
Format: `release/<version>`

Examples:
- `release/1.0.0`
- `release/1.1.0`
- `release/2.0.0-beta`

## Development Workflow

### 1. Starting New Work

#### Create Feature Branch
```bash
# Ensure you're on develop and up to date
git checkout develop
git pull origin develop

# Create and checkout new feature branch
git checkout -b feature/12-comprehensive-test-suite

# Push branch to remote
git push -u origin feature/12-comprehensive-test-suite
```

#### Link to GitHub Issue
- Branch name should include issue number when applicable
- Reference issue in commits: `git commit -m "feat: implement service provider (#2)"`

### 2. During Development

#### Commit Frequently
```bash
# Stage changes
git add .

# Commit with descriptive message
git commit -m "feat: add KeycloakService with OAuth2 flow"

# Push to remote
git push origin feature/12-comprehensive-test-suite
```

#### Commit Message Convention
Follow [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation only
- `style`: Code style (formatting, missing semi-colons, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

**Examples:**
```bash
git commit -m "feat: implement token refresh mechanism"
git commit -m "fix: resolve undefined email key error (#16)"
git commit -m "docs: update authentication flow diagram"
git commit -m "test: add unit tests for UserProvisioningService"
git commit -m "refactor: extract role mapping logic to service"
```

### 3. Keeping Branch Updated

#### Sync with Develop
```bash
# Fetch latest changes
git fetch origin

# Rebase on develop (preferred) or merge
git rebase origin/develop
# OR
git merge origin/develop

# Push updated branch
git push origin feature/12-comprehensive-test-suite --force-with-lease
```

### 4. Completing Work

#### Before Creating PR
```bash
# Run tests
composer test

# Check code style
composer lint

# Update documentation
# - Update CHANGELOG.md
# - Update Wiki if needed
# - Update .kennis documentation
```

#### Create Pull Request
```bash
# Push final changes
git push origin feature/12-comprehensive-test-suite

# Create PR via GitHub CLI
gh pr create \
  --title "feat: Comprehensive test suite implementation" \
  --body "Closes #12

## Changes
- Implemented unit tests for all services
- Added integration tests for authentication flow
- Achieved 85% code coverage

## Testing
- All tests passing
- Code coverage report included

## Documentation
- Updated test documentation in Wiki
- Added testing guidelines to .kennis" \
  --base develop
```

### 5. After PR Merge

#### Clean Up Local Branch
```bash
# Switch to develop
git checkout develop

# Pull latest changes
git pull origin develop

# Delete local feature branch
git branch -d feature/12-comprehensive-test-suite

# Delete remote branch (if not auto-deleted)
git push origin --delete feature/12-comprehensive-test-suite
```

## Documentation Structure

### Implementation Plans
**Location**: `.kennis/` folder  
**Purpose**: Technical implementation details, architecture decisions, internal documentation

**When to use:**
- Implementation plans and specifications
- Architecture decisions and rationale
- Technical design documents
- Internal development guides
- Code structure explanations

**Examples:**
- `.kennis/implementation-plan.md`
- `.kennis/architecture-decisions.md`
- `.kennis/MERMAID_GUIDELINES.md`
- `.kennis/INTERNATIONALIZATION.md`

### User & Developer Guides
**Location**: GitHub Wiki (`/Users/possebon/workspaces/kennis/krayin-crm/keycloak.wiki`)  
**Purpose**: User-facing documentation, installation guides, API documentation

**When to use:**
- Installation instructions
- Configuration guides
- API documentation and usage examples
- User guides and tutorials
- Troubleshooting guides
- FAQ and common issues

**Examples:**
- Wiki: Installation Guide
- Wiki: Authentication Flow
- Wiki: API Reference
- Wiki: Troubleshooting

### Documentation Update Workflow

#### For Technical Changes
1. Update `.kennis/` documentation during development
2. Commit with changes: `docs: update architecture decision for token handling`
3. Include in PR for review

#### For User-Facing Changes
1. Update Wiki pages after PR merge to develop
2. Use Mermaid diagrams where applicable (see [MERMAID_GUIDELINES.md](.kennis/MERMAID_GUIDELINES.md))
3. Link related Wiki pages
4. Update Wiki navigation/sidebar if needed

## Version Control

### Semantic Versioning
Follow [Semantic Versioning](https://semver.org/): `MAJOR.MINOR.PATCH`

- **MAJOR**: Breaking changes
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes (backward compatible)

### Creating a Release

#### 1. Create Release Branch
```bash
git checkout develop
git pull origin develop
git checkout -b release/1.0.0
```

#### 2. Prepare Release
```bash
# Update version in composer.json
# Update CHANGELOG.md with release date
# Run final tests
# Update documentation
```

#### 3. Merge to Main
```bash
git checkout main
git merge release/1.0.0
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin main --tags
```

#### 4. Merge Back to Develop
```bash
git checkout develop
git merge release/1.0.0
git push origin develop
```

#### 5. Clean Up
```bash
git branch -d release/1.0.0
```

## Best Practices

### Branch Management
- ✅ Always branch from `develop` for features/fixes
- ✅ Keep branches focused on single feature/fix
- ✅ Delete branches after merging
- ✅ Rebase or merge develop regularly to stay updated
- ❌ Never commit directly to `main` or `develop`
- ❌ Don't let branches become stale (merge or close within 2 weeks)

### Commit Practices
- ✅ Commit frequently with clear messages
- ✅ Use conventional commit format
- ✅ Reference issue numbers in commits
- ✅ Keep commits atomic (one logical change per commit)
- ❌ Don't commit untested code
- ❌ Don't commit sensitive information
- ❌ Don't use vague messages like "fix stuff" or "update code"

### Documentation Practices
- ✅ Update documentation with code changes
- ✅ Use appropriate location (.kennis vs Wiki)
- ✅ Include diagrams where helpful
- ✅ Keep documentation up to date
- ❌ Don't skip documentation
- ❌ Don't duplicate documentation in multiple places

### Code Review
- ✅ Request review before merging
- ✅ Address all review comments
- ✅ Ensure CI/CD passes
- ✅ Verify documentation is updated
- ❌ Don't merge without review (except hotfixes)
- ❌ Don't ignore failing tests

## Quick Reference

### Common Commands
```bash
# Create feature branch
git checkout -b feature/<name>

# Stage and commit
git add .
git commit -m "feat: description"

# Push branch
git push -u origin feature/<name>

# Create PR
gh pr create --base develop

# Update from develop
git fetch origin
git rebase origin/develop

# Merge PR and cleanup
git checkout develop
git pull origin develop
git branch -d feature/<name>
```

### Workflow Checklist
Before creating a PR:
- [ ] All tests passing
- [ ] Code follows style guidelines
- [ ] CHANGELOG.md updated
- [ ] Relevant documentation updated
- [ ] Commit messages follow convention
- [ ] Branch is up to date with develop
- [ ] Issue is referenced in PR description

## Related Documentation
- [CHANGELOG Management](../CLAUDE.md#changelog-md-management)
- [GitHub Issue Management](../CLAUDE.md#github-issue-management)
- [Mermaid Diagram Guidelines](MERMAID_GUIDELINES.md)
- [Internationalization Guidelines](INTERNATIONALIZATION.md)
