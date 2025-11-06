<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add Keycloak SSO fields to the users table.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Keycloak unique identifier
            $table->string('keycloak_id', 255)
                ->nullable()
                ->unique()
                ->after('id')
                ->comment('Keycloak user unique identifier (sub claim)');

            // Authentication provider (local or keycloak)
            $table->enum('auth_provider', ['local', 'keycloak'])
                ->default('local')
                ->after('password')
                ->comment('Authentication provider used by this user');

            // Encrypted refresh token for token renewal
            $table->text('keycloak_refresh_token')
                ->nullable()
                ->after('auth_provider')
                ->comment('Encrypted Keycloak refresh token');

            // Token expiration timestamp
            $table->timestamp('keycloak_token_expires_at')
                ->nullable()
                ->after('keycloak_refresh_token')
                ->comment('Keycloak access token expiration timestamp');

            // Add indexes for performance
            $table->index('keycloak_id', 'idx_users_keycloak_id');
            $table->index('auth_provider', 'idx_users_auth_provider');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Remove Keycloak SSO fields from the users table.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_users_keycloak_id');
            $table->dropIndex('idx_users_auth_provider');

            // Drop columns
            $table->dropColumn([
                'keycloak_id',
                'auth_provider',
                'keycloak_refresh_token',
                'keycloak_token_expires_at',
            ]);
        });
    }
};
