<?php

namespace Webkul\KeycloakSSO\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Webkul\User\Models\User;

/**
 * UserProvisioningService
 *
 * Handles automatic user provisioning and synchronization from Keycloak.
 * Creates, updates, and manages users based on Keycloak authentication data.
 */
class UserProvisioningService
{
    /**
     * Role mapping service instance.
     *
     * @var RoleMappingService
     */
    protected RoleMappingService $roleMappingService;

    /**
     * KeycloakService instance for role extraction.
     *
     * @var KeycloakService
     */
    protected KeycloakService $keycloakService;

    /**
     * Whether to auto-provision users.
     *
     * @var bool
     */
    protected bool $autoProvision;

    /**
     * Whether to sync user data on login.
     *
     * @var bool
     */
    protected bool $syncUserData;

    /**
     * Create a new UserProvisioningService instance.
     *
     * @param  RoleMappingService  $roleMappingService
     * @param  KeycloakService  $keycloakService
     * @param  bool  $autoProvision
     * @param  bool  $syncUserData
     */
    public function __construct(
        RoleMappingService $roleMappingService,
        KeycloakService $keycloakService,
        bool $autoProvision = true,
        bool $syncUserData = true
    ) {
        $this->roleMappingService = $roleMappingService;
        $this->keycloakService = $keycloakService;
        $this->autoProvision = $autoProvision;
        $this->syncUserData = $syncUserData;
    }

    /**
     * Find or create a user from Keycloak data.
     *
     * This is the main method used by the authentication controller.
     *
     * @param  array  $keycloakUser
     * @return User
     *
     * @throws \Exception
     */
    public function findOrCreateUser(array $keycloakUser): User
    {
        $keycloakId = $keycloakUser['sub'] ?? null;

        if (! $keycloakId) {
            throw new \Exception('Keycloak user ID (sub) not provided');
        }

        // Try to find existing user by Keycloak ID
        $user = User::where('keycloak_id', $keycloakId)->first();

        if ($user) {
            Log::info('Found existing user by Keycloak ID', [
                'user_id' => $user->id,
                'keycloak_id' => $keycloakId,
            ]);

            // Update user data if sync is enabled
            if ($this->syncUserData) {
                $user = $this->updateUserFromKeycloak($user, $keycloakUser);
            }

            return $user;
        }

        // Try to find by email (for linking existing accounts)
        $email = $keycloakUser['email'] ?? null;

        if ($email) {
            $user = User::where('email', $email)->first();

            if ($user) {
                Log::info('Found existing user by email, linking with Keycloak', [
                    'user_id' => $user->id,
                    'email' => $email,
                    'keycloak_id' => $keycloakId,
                ]);

                // Link existing user with Keycloak
                $user->keycloak_id = $keycloakId;
                $user->auth_provider = 'keycloak';
                $user->save();

                // Update user data
                $user = $this->updateUserFromKeycloak($user, $keycloakUser);

                return $user;
            }
        }

        // Create new user if auto-provisioning is enabled
        if ($this->autoProvision) {
            return $this->provisionUser($keycloakUser);
        }

        throw new \Exception('User not found and auto-provisioning is disabled');
    }

    /**
     * Provision (create) a new user from Keycloak data.
     *
     * @param  array  $keycloakUser
     * @return User
     *
     * @throws \Exception
     */
    public function provisionUser(array $keycloakUser): User
    {
        try {
            $keycloakId = $keycloakUser['sub'] ?? null;
            $email = $keycloakUser['email'] ?? null;
            $name = $keycloakUser['name'] ?? $keycloakUser['preferred_username'] ?? null;

            if (! $keycloakId) {
                throw new \Exception('Keycloak user ID (sub) is required');
            }

            if (! $email) {
                throw new \Exception('Email is required to create user');
            }

            Log::info('Provisioning new user from Keycloak', [
                'keycloak_id' => $keycloakId,
                'email' => $email,
                'name' => $name,
            ]);

            // Create user
            $user = new User();
            $user->keycloak_id = $keycloakId;
            $user->auth_provider = 'keycloak';
            $user->email = $email;
            $user->name = $name ?? $this->generateNameFromEmail($email);
            $user->password = Hash::make(Str::random(32)); // Random password (won't be used)
            $user->status = 1; // Active

            // Extract additional fields if available
            if (isset($keycloakUser['given_name'])) {
                $user->name = $keycloakUser['given_name'];
            }

            if (isset($keycloakUser['family_name'])) {
                $user->name = $user->name.' '.$keycloakUser['family_name'];
            }

            $user->save();

            Log::info('User provisioned successfully', [
                'user_id' => $user->id,
                'keycloak_id' => $keycloakId,
                'email' => $email,
            ]);

            // Sync roles from Keycloak
            $this->syncUserRoles($user, $keycloakUser);

            return $user;
        } catch (\Exception $e) {
            Log::error('Failed to provision user from Keycloak', [
                'keycloak_user' => $keycloakUser,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Update existing user from Keycloak data.
     *
     * @param  User  $user
     * @param  array  $keycloakData
     * @return User
     */
    public function updateUserFromKeycloak(User $user, array $keycloakData): User
    {
        try {
            $updated = false;

            // Update email if changed
            if (isset($keycloakData['email']) && $user->email !== $keycloakData['email']) {
                $user->email = $keycloakData['email'];
                $updated = true;
            }

            // Update name if available and different
            $keycloakName = $this->extractName($keycloakData);
            if ($keycloakName && $user->name !== $keycloakName) {
                $user->name = $keycloakName;
                $updated = true;
            }

            // Ensure auth provider is set to keycloak
            if ($user->auth_provider !== 'keycloak') {
                $user->auth_provider = 'keycloak';
                $updated = true;
            }

            // Ensure keycloak_id is set
            if (isset($keycloakData['sub']) && $user->keycloak_id !== $keycloakData['sub']) {
                $user->keycloak_id = $keycloakData['sub'];
                $updated = true;
            }

            if ($updated) {
                $user->save();

                Log::info('Updated user from Keycloak data', [
                    'user_id' => $user->id,
                    'keycloak_id' => $keycloakData['sub'] ?? null,
                ]);
            }

            // Sync roles
            $this->syncUserRoles($user, $keycloakData);

            return $user;
        } catch (\Exception $e) {
            Log::error('Failed to update user from Keycloak', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Synchronize user data from Keycloak.
     *
     * Alias for updateUserFromKeycloak for backward compatibility.
     *
     * @param  User  $user
     * @param  array  $keycloakData
     * @return User
     */
    public function syncUserData(User $user, array $keycloakData): User
    {
        return $this->updateUserFromKeycloak($user, $keycloakData);
    }

    /**
     * Sync user roles from Keycloak data.
     *
     * @param  User  $user
     * @param  array  $keycloakData
     * @return void
     */
    protected function syncUserRoles(User $user, array $keycloakData): void
    {
        try {
            // Extract roles from Keycloak data
            $keycloakRoles = $this->extractRoles($keycloakData);

            if (empty($keycloakRoles)) {
                Log::info('No roles found in Keycloak data, will use default', [
                    'user_id' => $user->id,
                ]);
            }

            // Sync roles using role mapping service
            $this->roleMappingService->syncRoles($user, $keycloakRoles);
        } catch (\Exception $e) {
            Log::error('Failed to sync user roles from Keycloak', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            // Don't throw exception - role sync failure shouldn't prevent login
        }
    }

    /**
     * Extract roles from Keycloak user data.
     *
     * @param  array  $keycloakData
     * @return array
     */
    protected function extractRoles(array $keycloakData): array
    {
        $roles = [];

        // Check realm_access roles
        if (isset($keycloakData['realm_access']['roles'])) {
            $roles = array_merge($roles, $keycloakData['realm_access']['roles']);
        }

        // Check resource_access roles (client-specific)
        if (isset($keycloakData['resource_access'])) {
            foreach ($keycloakData['resource_access'] as $resource => $access) {
                if (isset($access['roles'])) {
                    $roles = array_merge($roles, $access['roles']);
                }
            }
        }

        // Check direct roles claim
        if (isset($keycloakData['roles']) && is_array($keycloakData['roles'])) {
            $roles = array_merge($roles, $keycloakData['roles']);
        }

        return array_unique($roles);
    }

    /**
     * Extract name from Keycloak data.
     *
     * @param  array  $keycloakData
     * @return string|null
     */
    protected function extractName(array $keycloakData): ?string
    {
        // Prefer full name
        if (isset($keycloakData['name']) && ! empty($keycloakData['name'])) {
            return $keycloakData['name'];
        }

        // Build from first and last name
        $name = '';
        if (isset($keycloakData['given_name'])) {
            $name = $keycloakData['given_name'];
        }
        if (isset($keycloakData['family_name'])) {
            $name .= ($name ? ' ' : '').$keycloakData['family_name'];
        }

        if ($name) {
            return $name;
        }

        // Fallback to preferred_username
        if (isset($keycloakData['preferred_username'])) {
            return $keycloakData['preferred_username'];
        }

        return null;
    }

    /**
     * Generate name from email address.
     *
     * @param  string  $email
     * @return string
     */
    protected function generateNameFromEmail(string $email): string
    {
        $name = explode('@', $email)[0];
        $name = str_replace(['.', '_', '-'], ' ', $name);
        $name = ucwords($name);

        return $name;
    }

    /**
     * Check if auto-provisioning is enabled.
     *
     * @return bool
     */
    public function isAutoProvisionEnabled(): bool
    {
        return $this->autoProvision;
    }

    /**
     * Set auto-provisioning enabled state.
     *
     * @param  bool  $autoProvision
     * @return self
     */
    public function setAutoProvisionEnabled(bool $autoProvision): self
    {
        $this->autoProvision = $autoProvision;

        return $this;
    }

    /**
     * Check if user data sync is enabled.
     *
     * @return bool
     */
    public function isSyncUserDataEnabled(): bool
    {
        return $this->syncUserData;
    }

    /**
     * Set user data sync enabled state.
     *
     * @param  bool  $syncUserData
     * @return self
     */
    public function setSyncUserDataEnabled(bool $syncUserData): self
    {
        $this->syncUserData = $syncUserData;

        return $this;
    }
}
