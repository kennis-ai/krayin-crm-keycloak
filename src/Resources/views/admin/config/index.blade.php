@extends('admin::layouts.master')

@section('page_title')
    {{ __('keycloak::admin.config.title') }}
@stop

@section('content-wrapper')
    <div class="content full-page">
        <div class="page-header">
            <div class="page-title">
                <h1>
                    <i class="icon key-icon"></i>
                    {{ __('keycloak::admin.config.title') }}
                </h1>
            </div>

            <div class="page-action">
                <a href="{{ route('admin.keycloak.config.edit') }}" class="btn btn-primary">
                    {{ __('keycloak::admin.config.configure') }}
                </a>
            </div>
        </div>

        <div class="page-content">
            {{-- Status Cards --}}
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>{{ __('keycloak::admin.config.status') }}</h5>
                            <p class="h3">
                                @if($config['enabled'])
                                    <span class="badge badge-success">{{ __('keycloak::admin.config.enabled') }}</span>
                                @else
                                    <span class="badge badge-secondary">{{ __('keycloak::admin.config.disabled') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>{{ __('keycloak::admin.config.total_users') }}</h5>
                            <p class="h3">{{ $stats['total_users'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>{{ __('keycloak::admin.config.keycloak_users') }}</h5>
                            <p class="h3 text-primary">{{ $stats['keycloak_users'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>{{ __('keycloak::admin.config.local_users') }}</h5>
                            <p class="h3 text-info">{{ $stats['local_users'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Configuration Summary --}}
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('keycloak::admin.config.connection_info') }}</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td><strong>{{ __('keycloak::admin.config.base_url') }}:</strong></td>
                                        <td>{{ $config['base_url'] ?? 'Not configured' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('keycloak::admin.config.realm') }}:</strong></td>
                                        <td>{{ $config['realm'] ?? 'Not configured' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('keycloak::admin.config.client_id') }}:</strong></td>
                                        <td>{{ $config['client_id'] ?? 'Not configured' }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <button id="test-connection" class="btn btn-info mt-3">
                                <i class="icon refresh-icon"></i>
                                {{ __('keycloak::admin.config.test_connection') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('keycloak::admin.config.features') }}</h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td>{{ __('keycloak::admin.config.auto_provision') }}:</td>
                                        <td>
                                            @if($config['auto_provision_users'])
                                                <span class="badge badge-success">{{ __('keycloak::admin.config.enabled') }}</span>
                                            @else
                                                <span class="badge badge-secondary">{{ __('keycloak::admin.config.disabled') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('keycloak::admin.config.sync_user_data') }}:</td>
                                        <td>
                                            @if($config['sync_user_data'])
                                                <span class="badge badge-success">{{ __('keycloak::admin.config.enabled') }}</span>
                                            @else
                                                <span class="badge badge-secondary">{{ __('keycloak::admin.config.disabled') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('keycloak::admin.config.role_mapping') }}:</td>
                                        <td>
                                            @if($config['enable_role_mapping'])
                                                <span class="badge badge-success">{{ __('keycloak::admin.config.enabled') }}</span>
                                            @else
                                                <span class="badge badge-secondary">{{ __('keycloak::admin.config.disabled') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('keycloak::admin.config.fallback_local') }}:</td>
                                        <td>
                                            @if($config['fallback_on_error'])
                                                <span class="badge badge-success">{{ __('keycloak::admin.config.enabled') }}</span>
                                            @else
                                                <span class="badge badge-secondary">{{ __('keycloak::admin.config.disabled') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('keycloak::admin.config.quick_actions') }}</h4>
                        </div>
                        <div class="card-body">
                            <a href="{{ route('admin.keycloak.config.role-mappings') }}" class="btn btn-outline-primary">
                                <i class="icon users-icon"></i>
                                {{ __('keycloak::admin.config.manage_role_mappings') }}
                            </a>

                            <a href="{{ route('admin.keycloak.config.users') }}" class="btn btn-outline-info">
                                <i class="icon list-icon"></i>
                                {{ __('keycloak::admin.config.view_keycloak_users') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Keycloak Users --}}
            @if($recentKeycloakUsers->count() > 0)
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>{{ __('keycloak::admin.config.recent_users') }}</h4>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('keycloak::admin.config.name') }}</th>
                                            <th>{{ __('keycloak::admin.config.email') }}</th>
                                            <th>{{ __('keycloak::admin.config.role') }}</th>
                                            <th>{{ __('keycloak::admin.config.last_updated') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentKeycloakUsers as $user)
                                            <tr>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>{{ $user->role->name ?? 'N/A' }}</td>
                                                <td>{{ $user->updated_at->diffForHumans() }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop

@push('scripts')
<script>
    document.getElementById('test-connection').addEventListener('click', function() {
        const button = this;
        button.disabled = true;
        button.innerHTML = '<i class="icon spinner-icon"></i> Testing...';

        fetch('{{ route("admin.keycloak.config.test-connection") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✓ ' + data.message);
            } else {
                alert('✗ ' + data.message);
            }
        })
        .catch(error => {
            alert('✗ Connection test failed: ' + error.message);
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = '<i class="icon refresh-icon"></i> {{ __("keycloak::admin.config.test_connection") }}';
        });
    });
</script>
@endpush
