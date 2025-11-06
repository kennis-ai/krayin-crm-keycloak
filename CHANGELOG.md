# Changelog

All notable changes to the Krayin CRM Keycloak SSO Extension will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **Phase 4: Keycloak Service Integration** (2025-11-06)
  - KeycloakClient HTTP service for Keycloak API communication
  - Complete KeycloakService with authentication flow methods
  - Token management (access token, refresh token, validation)
  - User info retrieval with caching support
  - Single Logout (SLO) implementation
  - CSRF protection with state parameter validation
  - Custom exception hierarchy for error handling
  - Comprehensive logging for all operations

### Fixed
- None

### Changed
- None

### Security
- Added CSRF protection for OAuth callbacks via state parameter validation
- Implemented secure HTTP client with proper timeout and SSL verification
- Added comprehensive error handling for connection failures

---

### Planned Features
- Multi-realm support
- Advanced role mapping UI
- User group synchronization
- SAML 2.0 support
- Admin configuration panel
- Enhanced audit logging

## [1.0.0] - TBD

### Added
- Initial release
- Full OAuth 2.0 / OpenID Connect integration with Keycloak
- Automatic user provisioning from Keycloak
- User data synchronization
- Role mapping between Keycloak and Krayin CRM
- Automatic token refresh
- Single Logout (SLO) support
- Backward compatibility with local authentication
- Encrypted token storage
- CSRF protection
- Comprehensive error handling
- Fallback to local auth on errors
- Event-driven architecture
- Complete test suite
- Documentation and user guides

### Security
- Secure token storage with encryption
- SSL/TLS enforcement
- CSRF protection for OAuth callbacks
- Input validation and sanitization
- Audit logging for authentication events

---

## Version History

### Version Format

We use semantic versioning: `MAJOR.MINOR.PATCH`

- **MAJOR**: Breaking changes
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes (backward compatible)

### Release Notes

Detailed release notes for each version will be added here as they are released.

---

**Note**: This changelog will be updated with each release. For the latest changes, see the [commit history](../../commits).
