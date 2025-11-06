@extends('admin::layouts.master')

@section('page_title')
    {{ __('keycloak::admin.config.users_title') }}
@stop

@section('content-wrapper')
    <div class="content full-page">
        <div class="page-header">
            <div class="page-title">
                <h1>
                    <i class="icon users-icon"></i>
                    {{ __('keycloak::admin.config.users_title') }}
                </h1>
            </div>

            <div class="page-action">
                <a href="{{ route('admin.keycloak.config.index') }}" class="btn btn-secondary">
                    {{ __('keycloak::admin.config.back') }}
                </a>
            </div>
        </div>

        <div class="page-content">
            <div class="card">
                <div class="card-header">
                    <h4>
                        {{ __('keycloak::admin.config.keycloak_users_list') }}
                        <span class="badge badge-primary">{{ $keycloakUsers->total() }}</span>
                    </h4>
                </div>
                <div class="card-body">
                    @if($keycloakUsers->count() > 0)
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('keycloak::admin.config.id') }}</th>
                                    <th>{{ __('keycloak::admin.config.name') }}</th>
                                    <th>{{ __('keycloak::admin.config.email') }}</th>
                                    <th>{{ __('keycloak::admin.config.role') }}</th>
                                    <th>{{ __('keycloak::admin.config.keycloak_id') }}</th>
                                    <th>{{ __('keycloak::admin.config.last_updated') }}</th>
                                    <th>{{ __('keycloak::admin.config.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($keycloakUsers as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($user->role)
                                                <span class="badge badge-info">{{ $user->role->name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <code class="small">{{ substr($user->keycloak_id, 0, 20) }}...</code>
                                        </td>
                                        <td>{{ $user->updated_at->diffForHumans() }}</td>
                                        <td>
                                            <form method="POST"
                                                action="{{ route('admin.keycloak.config.users.sync', $user->id) }}"
                                                style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary"
                                                    title="{{ __('keycloak::admin.config.sync_user') }}">
                                                    <i class="icon refresh-icon"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-3">
                            {{ $keycloakUsers->links() }}
                        </div>
                    @else
                        <div class="alert alert-info">
                            {{ __('keycloak::admin.config.no_keycloak_users') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h4>{{ __('keycloak::admin.config.statistics') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="stat-box">
                                <h5>{{ __('keycloak::admin.config.total_keycloak_users') }}</h5>
                                <p class="h2">{{ $keycloakUsers->total() }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-box">
                                <h5>{{ __('keycloak::admin.config.active_this_week') }}</h5>
                                <p class="h2">
                                    {{ \Webkul\User\Models\User::where('auth_provider', 'keycloak')
                                        ->where('updated_at', '>=', now()->subWeek())
                                        ->count() }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-box">
                                <h5>{{ __('keycloak::admin.config.active_today') }}</h5>
                                <p class="h2">
                                    {{ \Webkul\User\Models\User::where('auth_provider', 'keycloak')
                                        ->where('updated_at', '>=', now()->startOfDay())
                                        ->count() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
