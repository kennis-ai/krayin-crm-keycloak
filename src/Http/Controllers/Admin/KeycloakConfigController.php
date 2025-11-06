<?php

namespace Webkul\KeycloakSSO\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Webkul\KeycloakSSO\Exceptions\KeycloakException;
use Webkul\KeycloakSSO\Helpers\ErrorHandler;
use Webkul\KeycloakSSO\Services\KeycloakService;
use Webkul\User\Models\User;
use Webkul\User\Repositories\RoleRepository;

/**
 * KeycloakConfigController
 *
 * Admin controller for managing Keycloak SSO configuration and settings.
 */
class KeycloakConfigController extends Controller
{
    /**
     * Role repository instance.
     *
     * @var RoleRepository
     */
    protected RoleRepository $roleRepository;

    /**
     * Create a new controller instance.
     *
     * @param  RoleRepository  $roleRepository
     */
    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * Display Keycloak configuration dashboard.
     *
     * @return View
     */
    public function index(): View
    {
        $config = config('keycloak');

        // Get statistics
        $stats = [
            'total_users' => User::count(),
            'keycloak_users' => User::where('auth_provider', 'keycloak')->count(),
            'local_users' => User::where('auth_provider', '!=', 'keycloak')
                ->orWhereNull('auth_provider')->count(),
            'enabled' => $config['enabled'] ?? false,
        ];

        // Get recent Keycloak users
        $recentKeycloakUsers = User::where('auth_provider', 'keycloak')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return view('keycloak::admin.config.index', compact('config', 'stats', 'recentKeycloakUsers'));
    }

    /**
     * Display Keycloak settings edit form.
     *
     * @return View
     */
    public function edit(): View
    {
        $config = config('keycloak');
        $roles = $this->roleRepository->all();

        return view('keycloak::admin.config.edit', compact('config', 'roles'));
    }

    /**
     * Update Keycloak configuration.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => 'required|boolean',
            'client_id' => 'required|string|max:255',
            'client_secret' => 'required|string|max:255',
            'base_url' => 'required|url|max:255',
            'realm' => 'required|string|max:255',
            'redirect_uri' => 'required|url|max:255',
            'auto_provision_users' => 'required|boolean',
            'sync_user_data' => 'required|boolean',
            'enable_role_mapping' => 'required|boolean',
            'allow_local_auth' => 'required|boolean',
            'fallback_on_error' => 'required|boolean',
            'role_mapping' => 'sometimes|array',
        ]);

        try {
            // Update environment file or configuration
            $this->updateKeycloakConfig($validated);

            // Clear config cache
            Cache::forget('keycloak_config');
            Config::set('keycloak', array_merge(config('keycloak'), $validated));

            Log::info('Keycloak configuration updated', [
                'admin_id' => auth()->guard('user')->id(),
                'enabled' => $validated['enabled'],
            ]);

            return redirect()
                ->route('admin.keycloak.config.index')
                ->with('success', trans('keycloak::admin.config.update_success'));
        } catch (\Exception $e) {
            ErrorHandler::handle($e, 'Failed to update Keycloak configuration', [
                'admin_id' => auth()->guard('user')->id(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', trans('keycloak::admin.config.update_failed'));
        }
    }

    /**
     * Test Keycloak connection.
     *
     * @return JsonResponse
     */
    public function testConnection(): JsonResponse
    {
        try {
            $config = config('keycloak');

            if (! $config['enabled']) {
                return response()->json([
                    'success' => false,
                    'message' => trans('keycloak::admin.config.test_disabled'),
                ], 400);
            }

            // Create Keycloak service instance with current config
            $keycloakService = new KeycloakService($config);

            // Test connection by attempting to get authorization URL
            $authUrl = $keycloakService->getAuthorizationUrl();

            if ($authUrl) {
                Log::info('Keycloak connection test successful', [
                    'admin_id' => auth()->guard('user')->id(),
                    'base_url' => $config['base_url'],
                    'realm' => $config['realm'],
                ]);

                return response()->json([
                    'success' => true,
                    'message' => trans('keycloak::admin.config.test_success'),
                    'data' => [
                        'base_url' => $config['base_url'],
                        'realm' => $config['realm'],
                        'auth_url' => $authUrl,
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => trans('keycloak::admin.config.test_failed'),
            ], 500);
        } catch (KeycloakException $e) {
            ErrorHandler::handle($e, 'Keycloak connection test failed', [
                'admin_id' => auth()->guard('user')->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => ErrorHandler::getUserMessage($e),
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            ErrorHandler::handle($e, 'Unexpected error during connection test', [
                'admin_id' => auth()->guard('user')->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => trans('keycloak::admin.config.test_error'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display role mapping configuration.
     *
     * @return View
     */
    public function roleMappings(): View
    {
        $config = config('keycloak');
        $roles = $this->roleRepository->all();
        $mappings = $config['role_mapping'] ?? [];

        return view('keycloak::admin.config.role-mappings', compact('mappings', 'roles'));
    }

    /**
     * Update role mappings.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function updateRoleMappings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mappings' => 'required|array',
            'mappings.*.keycloak_role' => 'required|string|max:255',
            'mappings.*.krayin_role' => 'required|string|max:255',
        ]);

        try {
            $mappings = [];
            foreach ($validated['mappings'] as $mapping) {
                $mappings[$mapping['keycloak_role']] = $mapping['krayin_role'];
            }

            // Update role mapping configuration
            $this->updateRoleMappingConfig($mappings);

            // Clear config cache
            Cache::forget('keycloak_config');

            Log::info('Keycloak role mappings updated', [
                'admin_id' => auth()->guard('user')->id(),
                'mappings_count' => count($mappings),
            ]);

            return redirect()
                ->route('admin.keycloak.config.role-mappings')
                ->with('success', trans('keycloak::admin.config.role_mappings_updated'));
        } catch (\Exception $e) {
            ErrorHandler::handle($e, 'Failed to update role mappings', [
                'admin_id' => auth()->guard('user')->id(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', trans('keycloak::admin.config.role_mappings_failed'));
        }
    }

    /**
     * Display Keycloak users list.
     *
     * @return View
     */
    public function users(): View
    {
        $keycloakUsers = User::where('auth_provider', 'keycloak')
            ->with('role')
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('keycloak::admin.config.users', compact('keycloakUsers'));
    }

    /**
     * Manually sync a user with Keycloak.
     *
     * @param  Request  $request
     * @param  int  $userId
     * @return RedirectResponse
     */
    public function syncUser(Request $request, int $userId): RedirectResponse
    {
        try {
            $user = User::findOrFail($userId);

            if ($user->auth_provider !== 'keycloak') {
                return redirect()
                    ->back()
                    ->with('error', trans('keycloak::admin.config.user_not_keycloak'));
            }

            // TODO: Implement manual sync logic
            // This would require getting fresh user data from Keycloak
            // and updating the local user record

            Log::info('Manual user sync requested', [
                'admin_id' => auth()->guard('user')->id(),
                'user_id' => $userId,
            ]);

            return redirect()
                ->back()
                ->with('info', trans('keycloak::admin.config.sync_not_implemented'));
        } catch (\Exception $e) {
            ErrorHandler::handle($e, 'Failed to sync user', [
                'admin_id' => auth()->guard('user')->id(),
                'user_id' => $userId,
            ]);

            return redirect()
                ->back()
                ->with('error', trans('keycloak::admin.config.sync_failed'));
        }
    }

    /**
     * Update Keycloak configuration in storage.
     *
     * @param  array  $config
     * @return void
     */
    protected function updateKeycloakConfig(array $config): void
    {
        // In a real implementation, this would update the .env file
        // or store configuration in a database table
        // For now, we'll just update the runtime config

        // Example: Update .env file (requires additional package like vlucas/phpdotenv)
        // $this->updateEnvFile([
        //     'KEYCLOAK_ENABLED' => $config['enabled'] ? 'true' : 'false',
        //     'KEYCLOAK_CLIENT_ID' => $config['client_id'],
        //     'KEYCLOAK_CLIENT_SECRET' => $config['client_secret'],
        //     'KEYCLOAK_BASE_URL' => $config['base_url'],
        //     'KEYCLOAK_REALM' => $config['realm'],
        //     'KEYCLOAK_REDIRECT_URI' => $config['redirect_uri'],
        // ]);

        // For now, log that this needs implementation
        Log::warning('Configuration update called - implement persistent storage', [
            'config_keys' => array_keys($config),
        ]);
    }

    /**
     * Update role mapping configuration.
     *
     * @param  array  $mappings
     * @return void
     */
    protected function updateRoleMappingConfig(array $mappings): void
    {
        // Similar to updateKeycloakConfig, this should persist to .env or database
        // For now, just log

        Log::warning('Role mapping update called - implement persistent storage', [
            'mappings_count' => count($mappings),
        ]);
    }
}
