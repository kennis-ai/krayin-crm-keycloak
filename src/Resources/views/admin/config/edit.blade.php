@extends('admin::layouts.master')

@section('page_title')
    {{ __('keycloak::admin.config.edit_title') }}
@stop

@section('content-wrapper')
    <div class="content full-page">
        <form method="POST" action="{{ route('admin.keycloak.config.update') }}" @submit.prevent="onSubmit">
            @csrf
            @method('PUT')

            <div class="page-header">
                <div class="page-title">
                    <h1>
                        <i class="icon key-icon"></i>
                        {{ __('keycloak::admin.config.edit_title') }}
                    </h1>
                </div>

                <div class="page-action">
                    <button type="submit" class="btn btn-primary">
                        {{ __('keycloak::admin.config.save') }}
                    </button>

                    <a href="{{ route('admin.keycloak.config.index') }}" class="btn btn-secondary">
                        {{ __('keycloak::admin.config.cancel') }}
                    </a>
                </div>
            </div>

            <div class="page-content">
                {{-- General Settings --}}
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('keycloak::admin.config.general_settings') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="enabled">
                                <input type="checkbox" id="enabled" name="enabled" value="1"
                                    {{ old('enabled', $config['enabled']) ? 'checked' : '' }}>
                                {{ __('keycloak::admin.config.enable_sso') }}
                            </label>
                            <p class="help-text">{{ __('keycloak::admin.config.enable_sso_help') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Connection Settings --}}
                <div class="card mt-4">
                    <div class="card-header">
                        <h4>{{ __('keycloak::admin.config.connection_settings') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="base_url">{{ __('keycloak::admin.config.base_url') }} *</label>
                            <input type="url" id="base_url" name="base_url" class="form-control"
                                value="{{ old('base_url', $config['base_url']) }}" required>
                            <p class="help-text">{{ __('keycloak::admin.config.base_url_help') }}</p>
                            @error('base_url')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="realm">{{ __('keycloak::admin.config.realm') }} *</label>
                            <input type="text" id="realm" name="realm" class="form-control"
                                value="{{ old('realm', $config['realm']) }}" required>
                            <p class="help-text">{{ __('keycloak::admin.config.realm_help') }}</p>
                            @error('realm')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="client_id">{{ __('keycloak::admin.config.client_id') }} *</label>
                            <input type="text" id="client_id" name="client_id" class="form-control"
                                value="{{ old('client_id', $config['client_id']) }}" required>
                            <p class="help-text">{{ __('keycloak::admin.config.client_id_help') }}</p>
                            @error('client_id')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="client_secret">{{ __('keycloak::admin.config.client_secret') }} *</label>
                            <input type="password" id="client_secret" name="client_secret" class="form-control"
                                value="{{ old('client_secret', $config['client_secret']) }}" required>
                            <p class="help-text">{{ __('keycloak::admin.config.client_secret_help') }}</p>
                            @error('client_secret')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="redirect_uri">{{ __('keycloak::admin.config.redirect_uri') }} *</label>
                            <input type="url" id="redirect_uri" name="redirect_uri" class="form-control"
                                value="{{ old('redirect_uri', $config['redirect_uri']) }}" required>
                            <p class="help-text">{{ __('keycloak::admin.config.redirect_uri_help') }}</p>
                            @error('redirect_uri')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Feature Settings --}}
                <div class="card mt-4">
                    <div class="card-header">
                        <h4>{{ __('keycloak::admin.config.feature_settings') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="auto_provision_users">
                                <input type="checkbox" id="auto_provision_users" name="auto_provision_users" value="1"
                                    {{ old('auto_provision_users', $config['auto_provision_users']) ? 'checked' : '' }}>
                                {{ __('keycloak::admin.config.auto_provision_users') }}
                            </label>
                            <p class="help-text">{{ __('keycloak::admin.config.auto_provision_users_help') }}</p>
                        </div>

                        <div class="form-group">
                            <label for="sync_user_data">
                                <input type="checkbox" id="sync_user_data" name="sync_user_data" value="1"
                                    {{ old('sync_user_data', $config['sync_user_data']) ? 'checked' : '' }}>
                                {{ __('keycloak::admin.config.sync_user_data') }}
                            </label>
                            <p class="help-text">{{ __('keycloak::admin.config.sync_user_data_help') }}</p>
                        </div>

                        <div class="form-group">
                            <label for="enable_role_mapping">
                                <input type="checkbox" id="enable_role_mapping" name="enable_role_mapping" value="1"
                                    {{ old('enable_role_mapping', $config['enable_role_mapping']) ? 'checked' : '' }}>
                                {{ __('keycloak::admin.config.enable_role_mapping') }}
                            </label>
                            <p class="help-text">{{ __('keycloak::admin.config.enable_role_mapping_help') }}</p>
                        </div>

                        <div class="form-group">
                            <label for="allow_local_auth">
                                <input type="checkbox" id="allow_local_auth" name="allow_local_auth" value="1"
                                    {{ old('allow_local_auth', $config['allow_local_auth']) ? 'checked' : '' }}>
                                {{ __('keycloak::admin.config.allow_local_auth') }}
                            </label>
                            <p class="help-text">{{ __('keycloak::admin.config.allow_local_auth_help') }}</p>
                        </div>

                        <div class="form-group">
                            <label for="fallback_on_error">
                                <input type="checkbox" id="fallback_on_error" name="fallback_on_error" value="1"
                                    {{ old('fallback_on_error', $config['fallback_on_error']) ? 'checked' : '' }}>
                                {{ __('keycloak::admin.config.fallback_on_error') }}
                            </label>
                            <p class="help-text">{{ __('keycloak::admin.config.fallback_on_error_help') }}</p>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        {{ __('keycloak::admin.config.save') }}
                    </button>

                    <a href="{{ route('admin.keycloak.config.index') }}" class="btn btn-secondary btn-lg">
                        {{ __('keycloak::admin.config.cancel') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
@stop
