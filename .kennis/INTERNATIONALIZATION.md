# Internationalization (i18n) Guidelines

## Overview
All implementations in this package must support multiple languages from the start. This document provides comprehensive guidelines for implementing i18n throughout the codebase.

## Supported Languages

### Required Languages
All user-facing text must be available in:

- **English (en)**: Primary language, always required
- **Brazilian Portuguese (pt_BR)**: Secondary language, always required

### Translation File Structure

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

## What to Translate

### User-Facing Content
- **Views**: All text in Blade templates
- **Flash Messages**: Success, error, info messages
- **Form Labels**: Input labels, placeholders, help text
- **Button Text**: Submit, cancel, action buttons
- **Validation Messages**: Custom validation error messages
- **Log Messages**: User-facing log entries
- **Email Templates**: All email content
- **Error Pages**: Error messages and descriptions
- **UI Elements**: Tooltips, modals, alerts

### Examples
```php
// Views - Blade templates
<h1>{{ __('keycloak::auth.login_title') }}</h1>
<p>{{ __('keycloak::auth.login_description') }}</p>

// Flash messages
return redirect()->back()->with('success', __('keycloak::auth.login_success'));

// Form labels
<label>{{ __('keycloak::forms.email') }}</label>

// Validation messages
'email.required' => __('keycloak::validation.email_required')
```

## What NOT to Translate

### Technical Content
- **Configuration keys**: `'client_id'`, `'redirect_uri'`, etc.
- **Database column names**: `keycloak_id`, `auth_provider`, etc.
- **Class names and method names**: `KeycloakService`, `handleCallback()`, etc.
- **Code comments**: Keep in English for developer consistency
- **Technical log entries**: Internal debugging logs
- **API endpoints and route names**: `/keycloak/callback`, etc.
- **Environment variables**: `KEYCLOAK_CLIENT_ID`, etc.

### Examples
```php
// Good - Technical content in English
Log::debug('Keycloak token refresh initiated', ['user_id' => $userId]);

// Good - User-facing content translated
Log::info(__('keycloak::logs.user_logged_in', ['name' => $user->name]));

// Bad - Don't translate technical logs
Log::debug(__('keycloak::debug.token_refresh'), ['user_id' => $userId]); // ❌
```

## Translation Key Convention

### Naming Standards
Use dot notation with descriptive, hierarchical keys:

```php
// Good - Descriptive and specific
'auth.login_success' => 'Login successful'
'auth.login_failed' => 'Login failed'
'auth.logout_confirmation' => 'Are you sure you want to logout?'
'user.provisioned' => 'User account created successfully'
'user.updated' => 'User information updated'
'errors.token_expired' => 'Your session has expired'

// Bad - Avoid single words or vague keys
'success' => 'Success' // ❌ Too generic
'error' => 'Error' // ❌ Too generic
'msg1' => 'Some message' // ❌ Not descriptive
```

### Key Organization
Organize keys by context:

```php
// auth.php
return [
    'login_title' => 'Login with Keycloak',
    'login_button' => 'Sign in',
    'logout_button' => 'Sign out',
    'logout_success' => 'You have been logged out successfully',
];

// errors.php
return [
    'connection_failed' => 'Failed to connect to Keycloak server',
    'token_invalid' => 'Invalid authentication token',
    'token_expired' => 'Your session has expired',
];

// admin.php
return [
    'config_title' => 'Keycloak Configuration',
    'config_saved' => 'Configuration saved successfully',
    'test_connection' => 'Test Connection',
];
```

## Usage in Code

### In Controllers
```php
public function login()
{
    try {
        $authUrl = $this->keycloakService->getAuthorizationUrl();
        return redirect($authUrl);
    } catch (KeycloakException $e) {
        return redirect()->back()->with('error', __('keycloak::errors.connection_failed'));
    }
}

public function callback(Request $request)
{
    $user = $this->keycloakService->handleCallback($request->code);
    
    return redirect()->route('dashboard')->with(
        'success',
        __('keycloak::auth.login_success', ['name' => $user->name])
    );
}
```

### In Views (Blade Templates)
```php
{{-- Simple translation --}}
<h1>{{ __('keycloak::auth.login_title') }}</h1>

{{-- Translation with parameters --}}
<p>{{ __('keycloak::auth.welcome_message', ['name' => $user->name]) }}</p>

{{-- Pluralization --}}
<p>{{ trans_choice('keycloak::messages.users_count', $count) }}</p>

{{-- With default fallback --}}
<span>{{ __('keycloak::optional.key', [], 'Default text') }}</span>
```

### With Parameters
```php
// Translation file: en/auth.php
'welcome_message' => 'Welcome back, :name!',
'users_online' => 'There are :count users online',
'last_login' => 'Last login: :date at :time',

// Usage
__('keycloak::auth.welcome_message', ['name' => $user->name])
__('keycloak::auth.users_online', ['count' => 42])
__('keycloak::auth.last_login', [
    'date' => $user->last_login->format('Y-m-d'),
    'time' => $user->last_login->format('H:i')
])
```

### Pluralization
```php
// Translation file: en/messages.php
'users_count' => '{0} No users|{1} One user|[2,*] :count users',
'minutes_ago' => '{1} :value minute ago|[2,*] :value minutes ago',

// Usage
trans_choice('keycloak::messages.users_count', 0) // "No users"
trans_choice('keycloak::messages.users_count', 1) // "One user"
trans_choice('keycloak::messages.users_count', 5) // "5 users"
```

## Testing Translations

### During Development
1. **Switch Locales**: Test both languages during development
   ```php
   App::setLocale('en');
   App::setLocale('pt_BR');
   ```

2. **Verify All Keys Exist**: Ensure all keys exist in both language files
   ```bash
   # Check for missing translations
   diff <(grep -r "^\s*'" src/Resources/lang/en/ | sort) \
        <(grep -r "^\s*'" src/Resources/lang/pt_BR/ | sort)
   ```

3. **Check Pluralization**: Verify pluralization rules work correctly
   ```php
   // Test in both languages
   App::setLocale('en');
   echo trans_choice('keycloak::messages.users_count', 5);
   
   App::setLocale('pt_BR');
   echo trans_choice('keycloak::messages.users_count', 5);
   ```

4. **Date/Time Formatting**: Check date/time formatting for each locale
   ```php
   Carbon::setLocale('en');
   Carbon::setLocale('pt_BR');
   ```

### Automated Testing
```php
/** @test */
public function it_has_all_translation_keys_in_both_languages()
{
    $enKeys = $this->getTranslationKeys('en');
    $ptKeys = $this->getTranslationKeys('pt_BR');
    
    $this->assertEquals($enKeys, $ptKeys, 'Translation keys mismatch');
}

/** @test */
public function it_displays_translated_flash_messages()
{
    App::setLocale('en');
    $response = $this->post('/keycloak/login');
    $response->assertSessionHas('success');
    $message = session('success');
    $this->assertStringContains('Login successful', $message);
    
    App::setLocale('pt_BR');
    $response = $this->post('/keycloak/login');
    $message = session('success');
    $this->assertStringContains('Login realizado com sucesso', $message);
}
```

## Quality Standards

### Completeness
- **Every key in English must have a pt_BR translation**
- No missing keys between language files
- All parameters must be present in both translations

### Accuracy
- **Natural and idiomatic translations**, not literal word-for-word
- Context-appropriate language
- Professional tone consistent with the application

### Consistency
- **Use consistent terminology** across the package
- Maintain glossary of common terms
- Follow Laravel's translation conventions

### Context
- **Provide context in comments** when translation might be ambiguous
  ```php
  // 'status' can mean different things
  'status' => 'Status', // User status: Active, Inactive, etc.
  
  // Or be more specific in the key
  'user_status' => 'User Status',
  'connection_status' => 'Connection Status',
  ```

## Common Translations Reference

### English (en)
```php
// Authentication
'login' => 'Login',
'logout' => 'Logout',
'login_with_keycloak' => 'Login with Keycloak',
'authentication_failed' => 'Authentication failed',

// Actions
'save' => 'Save',
'cancel' => 'Cancel',
'delete' => 'Delete',
'edit' => 'Edit',
'back' => 'Back',

// Status
'success' => 'Success',
'error' => 'Error',
'warning' => 'Warning',
'info' => 'Information',
```

### Brazilian Portuguese (pt_BR)
```php
// Authentication
'login' => 'Entrar',
'logout' => 'Sair',
'login_with_keycloak' => 'Entrar com Keycloak',
'authentication_failed' => 'Falha na autenticação',

// Actions
'save' => 'Salvar',
'cancel' => 'Cancelar',
'delete' => 'Excluir',
'edit' => 'Editar',
'back' => 'Voltar',

// Status
'success' => 'Sucesso',
'error' => 'Erro',
'warning' => 'Aviso',
'info' => 'Informação',
```

## Best Practices Summary

1. ✅ **Always translate user-facing content**
2. ✅ **Use descriptive, hierarchical translation keys**
3. ✅ **Test both languages during development**
4. ✅ **Keep translations natural and idiomatic**
5. ✅ **Maintain consistent terminology**
6. ✅ **Provide context for ambiguous terms**
7. ❌ **Never translate technical/code elements**
8. ❌ **Don't use generic keys like 'success' or 'error'**
9. ❌ **Don't skip translations - both languages required**

## Review Checklist

Before committing code with translations:

- [ ] All user-facing text has translation keys
- [ ] Translation keys exist in both `en` and `pt_BR`
- [ ] Keys follow dot notation convention
- [ ] Keys are descriptive and specific
- [ ] Parameters are properly formatted (`:param`)
- [ ] Pluralization is handled correctly
- [ ] No technical content is translated
- [ ] Translations are natural and idiomatic
- [ ] Context is provided for ambiguous terms
- [ ] Tests verify translations work correctly
