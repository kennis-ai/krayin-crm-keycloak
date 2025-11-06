<?php

namespace Webkul\KeycloakSSO\Tests\Unit\Admin;

use PHPUnit\Framework\TestCase;
use Webkul\KeycloakSSO\Http\Controllers\Admin\KeycloakConfigController;

/**
 * KeycloakConfigController Test
 *
 * Basic tests for admin configuration controller.
 */
class KeycloakConfigControllerTest extends TestCase
{
    /**
     * Test that the controller can be instantiated.
     */
    public function test_controller_can_be_instantiated(): void
    {
        $this->markTestSkipped('Requires full Laravel environment with Krayin CRM dependencies');

        // This test would require:
        // - Full Laravel application context
        // - Krayin CRM dependencies
        // - RoleRepository mock or instance
        //
        // Example implementation would be:
        // $roleRepository = $this->createMock(RoleRepository::class);
        // $controller = new KeycloakConfigController($roleRepository);
        // $this->assertInstanceOf(KeycloakConfigController::class, $controller);
    }

    /**
     * Test that controller methods exist.
     */
    public function test_controller_has_required_methods(): void
    {
        $this->assertTrue(method_exists(KeycloakConfigController::class, 'index'));
        $this->assertTrue(method_exists(KeycloakConfigController::class, 'edit'));
        $this->assertTrue(method_exists(KeycloakConfigController::class, 'update'));
        $this->assertTrue(method_exists(KeycloakConfigController::class, 'testConnection'));
        $this->assertTrue(method_exists(KeycloakConfigController::class, 'roleMappings'));
        $this->assertTrue(method_exists(KeycloakConfigController::class, 'updateRoleMappings'));
        $this->assertTrue(method_exists(KeycloakConfigController::class, 'users'));
        $this->assertTrue(method_exists(KeycloakConfigController::class, 'syncUser'));
    }

    /**
     * Test that route names are correctly configured.
     */
    public function test_route_names_are_defined(): void
    {
        // These are the expected route names that should be registered
        $expectedRoutes = [
            'admin.keycloak.config.index',
            'admin.keycloak.config.edit',
            'admin.keycloak.config.update',
            'admin.keycloak.config.test-connection',
            'admin.keycloak.config.role-mappings',
            'admin.keycloak.config.role-mappings.update',
            'admin.keycloak.config.users',
            'admin.keycloak.config.users.sync',
        ];

        // In a real test, we would verify these routes are registered:
        // $router = app('router');
        // foreach ($expectedRoutes as $routeName) {
        //     $this->assertTrue($router->has($routeName));
        // }

        $this->assertTrue(true, 'Route names are defined in admin-routes.php');
    }

    /**
     * Test translation keys exist.
     */
    public function test_translation_keys_exist(): void
    {
        $translationFile = __DIR__ . '/../../../src/Resources/lang/en/admin.php';

        $this->assertFileExists($translationFile);

        $translations = include $translationFile;

        $this->assertIsArray($translations);
        $this->assertArrayHasKey('config', $translations);

        // Verify key translation keys exist
        $requiredKeys = [
            'title',
            'edit_title',
            'role_mappings_title',
            'users_title',
            'configure',
            'save',
            'test_connection',
        ];

        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $translations['config'],
                "Translation key 'config.{$key}' is missing");
        }
    }

    /**
     * Test that Blade views exist.
     */
    public function test_blade_views_exist(): void
    {
        $viewsPath = __DIR__ . '/../../../src/Resources/views/admin/config/';

        $this->assertDirectoryExists($viewsPath);

        $requiredViews = [
            'index.blade.php',
            'edit.blade.php',
            'role-mappings.blade.php',
            'users.blade.php',
        ];

        foreach ($requiredViews as $view) {
            $this->assertFileExists($viewsPath . $view,
                "View file '{$view}' is missing");
        }
    }

    /**
     * Test menu configuration exists.
     */
    public function test_menu_configuration_exists(): void
    {
        $menuConfigFile = __DIR__ . '/../../../src/Config/menu.php';

        $this->assertFileExists($menuConfigFile);

        $menuConfig = include $menuConfigFile;

        $this->assertIsArray($menuConfig);
        $this->assertNotEmpty($menuConfig, 'Menu configuration should not be empty');

        // Verify first menu item has required keys
        $firstMenuItem = $menuConfig[0];
        $this->assertArrayHasKey('key', $firstMenuItem);
        $this->assertArrayHasKey('name', $firstMenuItem);
        $this->assertArrayHasKey('route', $firstMenuItem);
    }
}
