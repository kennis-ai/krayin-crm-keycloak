# Changelog

All notable changes to the Krayin CRM Keycloak SSO Extension will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **Phase 6: User Provisioning and Role Mapping** (2025-11-06)
  - Complete RoleMappingService for Keycloak role synchronization
  - Keycloak to Krayin role mapping with configurable mappings
  - Support for one-to-one and one-to-many role mappings
  - Default role fallback when no roles are mapped
  - Safe role assignment with database transactions
  - Role synchronization on every login (configurable)
  - Complete UserProvisioningService for automatic user management
  - Auto-creation of users from Keycloak authentication
  - Email-based account linking for existing users
  - User data synchronization (email, name, auth provider)
  - Role extraction from multiple Keycloak claim locations (realm_access, resource_access, direct roles claim)
  - Intelligent name extraction from Keycloak user data with fallbacks
  - Name generation from email for users without proper names
  - Comprehensive logging for all provisioning operations
  - Configurable auto-provisioning and sync behavior
  - Updated service provider with proper dependency injection

- **Phase 5: Authentication Controller** (2025-11-06)
  - Complete KeycloakAuthController with three main actions
  - Redirect to Keycloak login with CSRF protection
  - OAuth callback handling with user provisioning integration
  - Single Logout (SLO) implementation with token revocation
  - Session management (invalidation, regeneration, access token storage)
  - Comprehensive error handling with fallback support
  - Event firing for login success, login failure, and logout
  - Bilingual translation files (English and Brazilian Portuguese)
  - Graceful degradation when Keycloak is disabled or unavailable

- **Phase 4: Keycloak Service Integration** (2025-11-06)
  - KeycloakClient HTTP service for Keycloak API communication
  - Complete KeycloakService with authentication flow methods
  - Token management (access token, refresh token, validation)
  - User info retrieval with caching support
  - Single Logout (SLO) implementation
  - CSRF protection with state parameter validation
  - Custom exception hierarchy for error handling
  - Comprehensive logging for all operations

### Changed
- None

### Fixed
- None

### Security
- CSRF state parameter validation in OAuth callback
- Session invalidation and token regeneration on logout
- Secure error handling without exposing sensitive information
- Comprehensive logging for security auditing
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
