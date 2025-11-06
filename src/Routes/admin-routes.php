<?php

use Illuminate\Support\Facades\Route;
use Webkul\KeycloakSSO\Http\Controllers\Admin\KeycloakConfigController;

/*
|--------------------------------------------------------------------------
| Admin Routes for Keycloak SSO Configuration
|--------------------------------------------------------------------------
|
| Routes for managing Keycloak SSO configuration in the admin panel.
| These routes are protected by admin authentication middleware.
|
*/

Route::group([
    'prefix' => config('app.admin_path', 'admin'),
    'middleware' => ['web', 'admin'],
], function () {
    Route::prefix('keycloak')->name('admin.keycloak.')->group(function () {
        // Configuration Dashboard
        Route::get('config', [KeycloakConfigController::class, 'index'])
            ->name('config.index');

        // Edit Configuration
        Route::get('config/edit', [KeycloakConfigController::class, 'edit'])
            ->name('config.edit');

        Route::put('config', [KeycloakConfigController::class, 'update'])
            ->name('config.update');

        // Test Connection
        Route::post('config/test-connection', [KeycloakConfigController::class, 'testConnection'])
            ->name('config.test-connection');

        // Role Mappings
        Route::get('config/role-mappings', [KeycloakConfigController::class, 'roleMappings'])
            ->name('config.role-mappings');

        Route::put('config/role-mappings', [KeycloakConfigController::class, 'updateRoleMappings'])
            ->name('config.role-mappings.update');

        // Keycloak Users Management
        Route::get('config/users', [KeycloakConfigController::class, 'users'])
            ->name('config.users');

        Route::post('config/users/{id}/sync', [KeycloakConfigController::class, 'syncUser'])
            ->name('config.users.sync');
    });
});
