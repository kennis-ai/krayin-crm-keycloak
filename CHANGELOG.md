# Changelog

All notable changes to the Krayin CRM Keycloak SSO Extension will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **Phase 11: Admin Configuration UI** (2025-11-06)
  - Complete admin interface for Keycloak SSO management
  - KeycloakConfigController with 8 admin actions (index, edit, update, testConnection, roleMappings, updateRoleMappings, users, syncUser)
  - Admin dashboard with real-time statistics and quick actions
  - Configuration edit interface with comprehensive form validation
  - Connection testing functionality with real-time AJAX feedback
  - Role mapping management UI with dynamic mapping creation and deletion
  - Keycloak users list with pagination and detailed statistics
  - Manual user sync functionality (prepared for implementation)
  - Comprehensive Blade templates for all admin pages:
    - Dashboard (index.blade.php) with stats cards, connection info, features summary, and recent users
    - Configuration editor (edit.blade.php) with general, connection, and feature settings
    - Role mappings interface (role-mappings.blade.php) with dynamic forms and available roles list
    - Users list (users.blade.php) with pagination, statistics, and sync actions
  - Complete translation files (English and Brazilian Portuguese) for admin UI with 70+ translation keys
  - Admin routes file (admin-routes.php) with proper middleware protection
  - Menu integration configuration with nested menu structure
  - MENU_INTEGRATION.md guide with 3 integration options
  - JavaScript components for:
    - Connection testing with loading states
    - Dynamic role mapping form management
    - Form interactivity and validation
  - Statistics widgets displaying:
    - Total users vs Keycloak users vs Local users
    - Active users this week and today
    - Recent Keycloak user activity with roles and timestamps
  - Form validation rules for all configuration updates
  - Test connection endpoint returning JSON responses
  - Configuration persistence helpers (prepared for .env or database storage)
  - Basic unit tests for admin controller structure and views

- **Phase 10: Comprehensive Error Handling** (2025-11-06)
  - Created custom exception hierarchy for all error types
  - Added KeycloakTokenExpiredException for specific token expiration handling
  - Added KeycloakUserProvisioningException for user provisioning errors
  - Implemented centralized ErrorHandler helper class
  - Added retry mechanism with exponential backoff for transient failures
  - Implemented user-friendly error messages with i18n support (English and Portuguese)
  - Added comprehensive error translation files for all error scenarios
  - Enhanced debug mode configuration with granular error handling controls
  - Added error handling configuration (show_details, log_stack_traces, max_retries, retry_delay, exponential_backoff)
  - Improved error logging with automatic sensitive data sanitization
  - Enhanced KeycloakClient with retry logic and ErrorHandler integration
  - Enhanced UserProvisioningService with comprehensive error handling
  - Enhanced KeycloakAuthController with intelligent error handling and fallback
  - Implemented graceful fallback to local authentication on Keycloak failures
  - Added context-aware error messages for connection, authentication, and provisioning errors
  - Implemented comprehensive error logging without exposing sensitive information
  - Created basic unit tests for error handling functionality
  - Added error factory methods for common error scenarios
  - Implemented automatic log level determination based on error severity

- **Phase 9: Event System** (2025-11-06)
  - Complete event-driven architecture for Keycloak authentication lifecycle
  - KeycloakLoginSuccessful event (already implemented, verified)
  - KeycloakLoginFailed event (already implemented, verified)
  - KeycloakLogoutSuccessful event (already implemented, verified)
  - SyncKeycloakUser listener for post-login actions
  - Comprehensive login success logging with user details
  - Last login timestamp tracking
  - Extensible listener for custom post-login actions
  - HandleKeycloakLogout listener for post-logout cleanup
  - Session cleanup and cache clearing on logout
  - Logout event logging with timestamp tracking
  - Extensible listener for custom cleanup actions
  - LogLoginFailure listener for authentication failure handling
  - Security logging for failed authentication attempts
  - Failed login attempt tracking and rate limiting
  - IP-based failed attempt monitoring (15-minute window)
  - Automatic alerts after 5 failed attempts
  - Sanitization of sensitive data in logs
  - EventServiceProvider with complete event-listener mappings
  - Comprehensive error handling in all listeners
  - Silent failure handling (doesn't break authentication flow)
  - Event-driven hooks for third-party integrations

- **Phase 8: Routes and Integration** (2025-11-06)
  - Complete route definitions for Keycloak authentication flow
  - Login route with OAuth2 authorization redirect
  - Callback route for handling OAuth2 responses
  - Logout route with Single Logout (SLO) support
  - Conditional route registration (only when Keycloak is enabled)
  - Complete login button view component with Blade template
  - Responsive and accessible login button design
  - Keycloak icon included in button
  - Divider component with "Or continue with" text
  - Dark mode support for login button
  - Hover, focus, and active states for better UX
  - Customizable button with optional parameters
  - Comprehensive integration documentation
  - Step-by-step integration guide for Krayin login page
  - Customization examples and troubleshooting guide
  - New translation keys for login button
  - Route protection with web middleware
  - Logout route protected with auth middleware
  - Alternative route definitions commented for flexibility

- **Phase 7: Middleware and Guards** (2025-11-06)
  - Complete KeycloakAuthenticate middleware for session validation
  - Token validation with Keycloak introspection
  - Automatic redirect to login for unauthenticated users
  - Support for local authentication alongside Keycloak
  - Graceful handling of invalid sessions with user logout
  - Configurable auto-redirect to Keycloak login
  - Complete KeycloakTokenRefresh middleware for automatic token refresh
  - Proactive token refresh before expiration (configurable threshold)
  - Seamless token refresh without user interruption
  - Automatic session update with new tokens
  - Configurable behavior on refresh failure (logout or continue)
  - Token expiration checking with configurable threshold (default: 5 minutes)
  - New translation keys for session expiration and token refresh failures
  - Comprehensive logging for all middleware operations
  - Support for custom authentication guards
  - Intended URL preservation for post-login redirects

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
