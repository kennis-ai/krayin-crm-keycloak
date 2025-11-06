# Krayin CRM Keycloak SSO Extension

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue.svg)](https://php.net)
[![Laravel](https://img.shields.io/badge/laravel-%3E%3D10.0-red.svg)](https://laravel.com)
[![Krayin CRM](https://img.shields.io/badge/krayin-%3E%3D2.0-green.svg)](https://krayincrm.com)

Enterprise-grade Keycloak Single Sign-On (SSO) integration for Krayin CRM. Seamlessly integrate your Krayin CRM with Keycloak identity and access management system.

## Features

✨ **Core Features**
- 🔐 Full OAuth 2.0 / OpenID Connect integration with Keycloak
- 🔄 Automatic user provisioning and synchronization
- 👥 Role mapping between Keycloak and Krayin CRM
- 🔁 Token refresh automation
- 🚪 Single Logout (SLO) support
- ⚡ Backward compatible with local authentication
- 🎯 Zero modification to Krayin CRM core

🛡️ **Security Features**
- Encrypted token storage
- CSRF protection
- SSL/TLS support
- Secure session management
- Audit logging

🎛️ **Configuration Options**
- Toggle SSO on/off
- User auto-provisioning
- Role mapping configuration
- Fallback to local auth
- Custom redirect URIs

## Requirements

- PHP >= 8.2
- Laravel >= 10.0
- Krayin CRM >= 2.0
- Keycloak Server >= 20.0
- Composer >= 2.5

## Installation

### Step 1: Install via Composer

```bash
composer require webkul/laravel-keycloak-sso
```

### Step 2: Publish Configuration

```bash
php artisan vendor:publish --provider="Webkul\KeycloakSSO\Providers\KeycloakSSOServiceProvider"
```

### Step 3: Run Migrations

```bash
php artisan migrate
```

### Step 4: Configure Environment

Add the following to your `.env` file:

```env
# Enable Keycloak SSO
KEYCLOAK_ENABLED=true

# Keycloak Server Configuration
KEYCLOAK_CLIENT_ID=your-client-id
KEYCLOAK_CLIENT_SECRET=your-client-secret
KEYCLOAK_BASE_URL=https://keycloak.example.com
KEYCLOAK_REALM=master
KEYCLOAK_REDIRECT_URI=https://crm.example.com/admin/auth/keycloak/callback

# Feature Flags
KEYCLOAK_AUTO_PROVISION=true
KEYCLOAK_SYNC_USER_DATA=true
KEYCLOAK_ROLE_MAPPING=true

# Fallback Options
KEYCLOAK_ALLOW_LOCAL_AUTH=true
KEYCLOAK_FALLBACK_ON_ERROR=true
```

### Step 5: Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
```

## Configuration

### Keycloak Server Setup

1. Create a new client in Keycloak
2. Set **Client Protocol**: `openid-connect`
3. Set **Access Type**: `confidential`
4. Enable **Standard Flow**
5. Add **Valid Redirect URIs**: `https://your-crm-domain.com/admin/auth/keycloak/callback`
6. Get your **Client ID** and **Client Secret**

### Role Mapping

Edit `config/keycloak.php` to map Keycloak roles to Krayin CRM roles:

```php
'role_mapping' => [
    'keycloak_admin' => 'Administrator',
    'keycloak_manager' => 'Manager',
    'keycloak_user' => 'Sales',
],
```

## Usage

### Login with Keycloak

Once configured, users will see a "Login with Keycloak" button on the login page. Clicking it will redirect them to Keycloak for authentication.

### Local Authentication Fallback

If `KEYCLOAK_ALLOW_LOCAL_AUTH=true`, users can still log in using their local Krayin credentials. This is useful for:
- Keycloak server downtime
- Admin access recovery
- Testing and development

### User Provisioning

When a user logs in via Keycloak for the first time:
1. User account is automatically created in Krayin
2. User data is synchronized from Keycloak
3. Roles are mapped and assigned
4. User is redirected to the dashboard

Subsequent logins will sync user data if `KEYCLOAK_SYNC_USER_DATA=true`.

## API Reference

For detailed API documentation, see [API_REFERENCE.md](.kennis/API_REFERENCE.md)

## Architecture

For technical architecture details, see [ARCHITECTURE.md](.kennis/ARCHITECTURE.md)

## Development

### Project Structure

```
keycloak/
├── src/                    # Source code
│   ├── Config/            # Configuration files
│   ├── Http/              # Controllers, middleware, requests
│   ├── Services/          # Business logic
│   ├── Providers/         # Service providers
│   └── ...
├── tests/                 # Test suite
├── .kennis/              # Technical documentation
└── composer.json         # Package definition
```

### Running Tests

```bash
# Run all tests
composer test

# Run with coverage
composer test-coverage

# Run specific test suite
./vendor/bin/phpunit tests/Unit
./vendor/bin/phpunit tests/Feature
```

### Code Style

This project follows PSR-12 coding standards.

```bash
# Check code style
composer check-style

# Fix code style
composer fix-style
```

## Troubleshooting

### Issue: "Login with Keycloak" button not showing

**Solution**: Check that `KEYCLOAK_ENABLED=true` in your `.env` file and clear cache.

### Issue: OAuth callback error

**Solution**: Verify your redirect URI in Keycloak matches `KEYCLOAK_REDIRECT_URI` in `.env`.

### Issue: Token expired errors

**Solution**: The extension automatically refreshes tokens. Check Keycloak server connectivity.

### Issue: User roles not mapping correctly

**Solution**: Review role mapping in `config/keycloak.php` and ensure Keycloak roles match.

For more troubleshooting tips, see the [Wiki](../../wiki).

## Documentation

- [Implementation Plan](.kennis/IMPLEMENTATION_PLAN.md)
- [Architecture Documentation](.kennis/ARCHITECTURE.md)
- [API Reference](.kennis/API_REFERENCE.md)
- [Development Notes](.kennis/DEVELOPMENT_NOTES.md)
- [Wiki](https://github.com/kennis-ai/krayin-crm-keycloak/wiki) - User guides and tutorials

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Commit your changes: `git commit -am 'Add new feature'`
4. Push to the branch: `git push origin feature/your-feature`
5. Submit a pull request

Please ensure:
- Code follows PSR-12 standards
- All tests pass
- New features include tests
- Documentation is updated

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

## Security

If you discover a security vulnerability, please email security@kennis.ai instead of using the issue tracker.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Credits

- **Author**: Kennis AI Team
- **Company**: [Kennis AI](https://kennis.ai)
- **Repository**: [kennis-ai/krayin-crm-keycloak](https://github.com/kennis-ai/krayin-crm-keycloak)
- Built for [Krayin CRM](https://krayincrm.com)
- Powered by [Keycloak](https://www.keycloak.org)

## Support

- 📖 [Documentation](https://github.com/kennis-ai/krayin-crm-keycloak/wiki)
- 🐛 [Issue Tracker](https://github.com/kennis-ai/krayin-crm-keycloak/issues)
- 💬 [Discussions](https://github.com/kennis-ai/krayin-crm-keycloak/discussions)
- 📧 Email Support: support@kennis.ai

## Roadmap

- [ ] Multi-realm support
- [ ] Advanced role mapping UI
- [ ] User group synchronization
- [ ] SAML 2.0 support
- [ ] Admin configuration panel
- [ ] Enhanced audit logging

---

Made with ❤️ for the Krayin CRM community
