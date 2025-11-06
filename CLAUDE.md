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
