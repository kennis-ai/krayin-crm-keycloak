@extends('admin::layouts.master')

@section('page_title')
    {{ __('keycloak::admin.config.role_mappings_title') }}
@stop

@section('content-wrapper')
    <div class="content full-page">
        <form method="POST" action="{{ route('admin.keycloak.config.role-mappings.update') }}">
            @csrf
            @method('PUT')

            <div class="page-header">
                <div class="page-title">
                    <h1>
                        <i class="icon users-icon"></i>
                        {{ __('keycloak::admin.config.role_mappings_title') }}
                    </h1>
                </div>

                <div class="page-action">
                    <button type="submit" class="btn btn-primary">
                        {{ __('keycloak::admin.config.save_mappings') }}
                    </button>

                    <a href="{{ route('admin.keycloak.config.index') }}" class="btn btn-secondary">
                        {{ __('keycloak::admin.config.back') }}
                    </a>
                </div>
            </div>

            <div class="page-content">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('keycloak::admin.config.role_mappings_info') }}</h4>
                    </div>
                    <div class="card-body">
                        <p>{{ __('keycloak::admin.config.role_mappings_description') }}</p>

                        <div id="role-mappings-container">
                            @forelse($mappings as $keycloakRole => $krayinRole)
                                <div class="role-mapping-row mb-3">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <label>{{ __('keycloak::admin.config.keycloak_role') }}</label>
                                            <input type="text" name="mappings[{{ $loop->index }}][keycloak_role]"
                                                class="form-control" value="{{ $keycloakRole }}" required>
                                        </div>
                                        <div class="col-md-1 text-center mt-4">
                                            <i class="icon arrow-right-icon"></i>
                                        </div>
                                        <div class="col-md-5">
                                            <label>{{ __('keycloak::admin.config.krayin_role') }}</label>
                                            <select name="mappings[{{ $loop->index }}][krayin_role]"
                                                class="form-control" required>
                                                <option value="">{{ __('keycloak::admin.config.select_role') }}</option>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->name }}"
                                                        {{ $role->name === $krayinRole ? 'selected' : '' }}>
                                                        {{ $role->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-danger mt-4 remove-mapping">
                                                <i class="icon trash-icon"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted">{{ __('keycloak::admin.config.no_mappings') }}</p>
                            @endforelse
                        </div>

                        <button type="button" id="add-mapping" class="btn btn-success mt-3">
                            <i class="icon plus-icon"></i>
                            {{ __('keycloak::admin.config.add_mapping') }}
                        </button>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h4>{{ __('keycloak::admin.config.available_roles') }}</h4>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('keycloak::admin.config.role_name') }}</th>
                                    <th>{{ __('keycloak::admin.config.users_count') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $role)
                                    <tr>
                                        <td>{{ $role->name }}</td>
                                        <td>{{ $role->users->count() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        {{ __('keycloak::admin.config.save_mappings') }}
                    </button>

                    <a href="{{ route('admin.keycloak.config.index') }}" class="btn btn-secondary btn-lg">
                        {{ __('keycloak::admin.config.back') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
@stop

@push('scripts')
<script>
    let mappingIndex = {{ count($mappings) }};

    document.getElementById('add-mapping').addEventListener('click', function() {
        const container = document.getElementById('role-mappings-container');
        const roleOptions = `
            <option value="">{{ __('keycloak::admin.config.select_role') }}</option>
            @foreach($roles as $role)
                <option value="{{ $role->name }}">{{ $role->name }}</option>
            @endforeach
        `;

        const newMapping = `
            <div class="role-mapping-row mb-3">
                <div class="row">
                    <div class="col-md-5">
                        <label>{{ __('keycloak::admin.config.keycloak_role') }}</label>
                        <input type="text" name="mappings[${mappingIndex}][keycloak_role]"
                            class="form-control" required>
                    </div>
                    <div class="col-md-1 text-center mt-4">
                        <i class="icon arrow-right-icon"></i>
                    </div>
                    <div class="col-md-5">
                        <label>{{ __('keycloak::admin.config.krayin_role') }}</label>
                        <select name="mappings[${mappingIndex}][krayin_role]"
                            class="form-control" required>
                            ${roleOptions}
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger mt-4 remove-mapping">
                            <i class="icon trash-icon"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', newMapping);
        mappingIndex++;
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-mapping') || e.target.closest('.remove-mapping')) {
            const button = e.target.classList.contains('remove-mapping') ? e.target : e.target.closest('.remove-mapping');
            const row = button.closest('.role-mapping-row');
            row.remove();
        }
    });
</script>
@endpush
