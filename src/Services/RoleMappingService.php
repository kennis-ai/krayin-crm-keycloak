<?php

namespace Webkul\KeycloakSSO\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\User\Models\Role;
use Webkul\User\Models\User;

/**
 * RoleMappingService
 *
 * Maps Keycloak roles to Krayin CRM roles and manages user role assignments.
 * Handles role synchronization between Keycloak and Krayin CRM.
 */
class RoleMappingService
{
    /**
     * Role mapping configuration.
     *
     * Maps Keycloak role names to Krayin CRM role names.
     *
     * @var array
     */
    protected array $roleMapping;

    /**
     * Default role name for users without mapped roles.
     *
     * @var string
     */
    protected string $defaultRole;

    /**
     * Whether to sync roles on every login.
     *
     * @var bool
     */
    protected bool $syncRoles;

    /**
     * Create a new RoleMappingService instance.
     *
     * @param  array  $roleMapping
     * @param  string  $defaultRole
     * @param  bool  $syncRoles
     */
    public function __construct(
        array $roleMapping = [],
        string $defaultRole = 'Sales',
        bool $syncRoles = true
    ) {
        $this->roleMapping = $roleMapping;
        $this->defaultRole = $defaultRole;
        $this->syncRoles = $syncRoles;
    }

    /**
     * Map Keycloak roles to Krayin CRM role names.
     *
     * @param  array  $keycloakRoles
     * @return array Array of Krayin CRM role names
     */
    public function mapKeycloakRolesToKrayin(array $keycloakRoles): array
    {
        $mappedRoles = [];

        foreach ($keycloakRoles as $keycloakRole) {
            // Check if there's a mapping for this Keycloak role
            if (isset($this->roleMapping[$keycloakRole])) {
                $krayinRole = $this->roleMapping[$keycloakRole];

                // Handle both string and array mappings
                if (is_array($krayinRole)) {
                    $mappedRoles = array_merge($mappedRoles, $krayinRole);
                } else {
                    $mappedRoles[] = $krayinRole;
                }
            }
        }

        // Remove duplicates
        $mappedRoles = array_unique($mappedRoles);

        // If no roles mapped, use default role
        if (empty($mappedRoles)) {
            Log::info('No Keycloak roles mapped, using default role', [
                'keycloak_roles' => $keycloakRoles,
                'default_role' => $this->defaultRole,
            ]);

            $mappedRoles[] = $this->defaultRole;
        }

        Log::info('Mapped Keycloak roles to Krayin roles', [
            'keycloak_roles' => $keycloakRoles,
            'mapped_roles' => $mappedRoles,
        ]);

        return $mappedRoles;
    }

    /**
     * Get Krayin CRM Role models from role names.
     *
     * @param  array  $roleNames
     * @return \Illuminate\Support\Collection
     */
    public function getRolesByNames(array $roleNames): \Illuminate\Support\Collection
    {
        return Role::whereIn('name', $roleNames)->get();
    }

    /**
     * Assign roles to a user.
     *
     * Replaces existing roles with new ones.
     *
     * @param  User  $user
     * @param  array  $roleNames Array of Krayin CRM role names
     * @return void
     */
    public function assignRoles(User $user, array $roleNames): void
    {
        try {
            // Get Role models
            $roles = $this->getRolesByNames($roleNames);

            if ($roles->isEmpty()) {
                Log::warning('No valid roles found for assignment', [
                    'user_id' => $user->id,
                    'role_names' => $roleNames,
                ]);

                // Try to use default role
                $defaultRole = Role::where('name', $this->defaultRole)->first();

                if ($defaultRole) {
                    $roles = collect([$defaultRole]);
                } else {
                    Log::error('Default role not found', [
                        'default_role' => $this->defaultRole,
                    ]);
                    return;
                }
            }

            // Get role IDs
            $roleIds = $roles->pluck('id')->toArray();

            // Sync roles (replaces existing)
            DB::transaction(function () use ($user, $roleIds) {
                $user->role_id = $roleIds[0]; // Primary role
                $user->save();

                // If user has roles relationship, sync all roles
                if (method_exists($user, 'roles')) {
                    $user->roles()->sync($roleIds);
                }
            });

            Log::info('Assigned roles to user', [
                'user_id' => $user->id,
                'role_names' => $roleNames,
                'role_ids' => $roleIds,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to assign roles to user', [
                'user_id' => $user->id,
                'role_names' => $roleNames,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Synchronize user roles from Keycloak.
     *
     * Maps Keycloak roles to Krayin roles and assigns them to the user.
     *
     * @param  User  $user
     * @param  array  $keycloakRoles
     * @return void
     */
    public function syncRoles(User $user, array $keycloakRoles): void
    {
        // Check if role sync is enabled
        if (! $this->syncRoles) {
            Log::debug('Role sync is disabled', [
                'user_id' => $user->id,
            ]);
            return;
        }

        // Map Keycloak roles to Krayin roles
        $krayinRoles = $this->mapKeycloakRolesToKrayin($keycloakRoles);

        // Assign roles to user
        $this->assignRoles($user, $krayinRoles);

        Log::info('Synchronized user roles from Keycloak', [
            'user_id' => $user->id,
            'keycloak_roles' => $keycloakRoles,
            'krayin_roles' => $krayinRoles,
        ]);
    }

    /**
     * Get the role mapping configuration.
     *
     * @return array
     */
    public function getRoleMapping(): array
    {
        return $this->roleMapping;
    }

    /**
     * Set the role mapping configuration.
     *
     * @param  array  $roleMapping
     * @return self
     */
    public function setRoleMapping(array $roleMapping): self
    {
        $this->roleMapping = $roleMapping;

        return $this;
    }

    /**
     * Get the default role name.
     *
     * @return string
     */
    public function getDefaultRole(): string
    {
        return $this->defaultRole;
    }

    /**
     * Set the default role name.
     *
     * @param  string  $defaultRole
     * @return self
     */
    public function setDefaultRole(string $defaultRole): self
    {
        $this->defaultRole = $defaultRole;

        return $this;
    }

    /**
     * Check if role sync is enabled.
     *
     * @return bool
     */
    public function isSyncEnabled(): bool
    {
        return $this->syncRoles;
    }

    /**
     * Set whether to sync roles.
     *
     * @param  bool  $syncRoles
     * @return self
     */
    public function setSyncEnabled(bool $syncRoles): self
    {
        $this->syncRoles = $syncRoles;

        return $this;
    }
}
