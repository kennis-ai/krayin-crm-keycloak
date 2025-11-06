{{--
    Keycloak SSO Login Button Component

    This component displays a "Login with Keycloak" button for Single Sign-On authentication.
    Include this component in your login page to enable Keycloak authentication.

    Usage:
    @include('keycloak-sso::login-button')

    Optional parameters:
    - showDivider (bool): Show divider line above button (default: true)
    - buttonClass (string): Additional CSS classes for the button
    - containerClass (string): Additional CSS classes for the container
--}}

@if(config('keycloak.enabled'))
    <div class="keycloak-sso-login {{ $containerClass ?? '' }}">
        @if($showDivider ?? true)
            <div class="divider">
                <span>{{ __('keycloak-sso::auth.or_login_with') }}</span>
            </div>
        @endif

        <a href="{{ route('admin.keycloak.login') }}"
           class="btn btn-keycloak {{ $buttonClass ?? '' }}"
           role="button">
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M11.5 0L0 6v12.5l11.5 5.5 11.5-5.5V6L11.5 0zm0 2.2l9.3 4.3-9.3 4.4-9.3-4.4 9.3-4.3zM2 8.5l9 4.2v7.8l-9-4.3V8.5zm11 12l9-4.3V8.5l-9 4.2v7.8z"/>
            </svg>
            <span>{{ __('keycloak-sso::auth.login_with_keycloak') }}</span>
        </a>
    </div>

    <style>
        .keycloak-sso-login {
            margin-top: 1.5rem;
        }

        .keycloak-sso-login .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
            color: #6c757d;
            font-size: 0.875rem;
        }

        .keycloak-sso-login .divider::before,
        .keycloak-sso-login .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #dee2e6;
        }

        .keycloak-sso-login .divider span {
            padding: 0 1rem;
            white-space: nowrap;
        }

        .btn-keycloak {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            line-height: 1.5;
            color: #ffffff;
            background-color: #0078d4;
            border: 1px solid #0078d4;
            border-radius: 0.375rem;
            text-decoration: none;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
        }

        .btn-keycloak:hover {
            background-color: #106ebe;
            border-color: #106ebe;
            color: #ffffff;
            text-decoration: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .btn-keycloak:focus {
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(0, 120, 212, 0.25);
        }

        .btn-keycloak:active {
            background-color: #005a9e;
            border-color: #005a9e;
        }

        .btn-keycloak .icon {
            flex-shrink: 0;
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .keycloak-sso-login .divider {
                color: #adb5bd;
            }

            .keycloak-sso-login .divider::before,
            .keycloak-sso-login .divider::after {
                border-bottom-color: #495057;
            }
        }
    </style>
@endif
