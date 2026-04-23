<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Enables Spatie Permission's Teams feature, rewired to use tenant_id.
 *
 * Effect on existing schema:
 *   roles                       + tenant_id (nullable, FK, indexed)
 *   model_has_roles             + tenant_id (nullable, part of PK)
 *   model_has_permissions       + tenant_id (nullable, part of PK)
 *
 * All existing rows are backfilled to Default Tenant so current auth keeps
 * working. Unique index on `roles.(name, guard_name)` is replaced with a
 * composite (tenant_id, name, guard_name) so two tenants can share role names
 * ("admin", "lawyer", …) without collision.
 *
 * permissions stays shared — NO tenant_id there.
 */
return new class extends Migration {
    public function up(): void
    {
        $teamsConfig = config('permission.column_names.team_foreign_key', 'tenant_id');
        $defaultId   = (int) env('TENANCY_DEFAULT_TENANT_ID', 1);

        // --- roles -----------------------------------------------------------
        if (Schema::hasTable('roles') && ! Schema::hasColumn('roles', $teamsConfig)) {
            Schema::table('roles', function (Blueprint $table) use ($teamsConfig) {
                $table->unsignedBigInteger($teamsConfig)->nullable()->after('id');
                $table->index($teamsConfig, "roles_{$teamsConfig}_index");
            });

            // Drop the old (name, guard_name) unique index if it exists.
            // Spatie's default name is `roles_name_guard_name_unique`.
            try {
                DB::statement('ALTER TABLE roles DROP INDEX roles_name_guard_name_unique');
            } catch (\Throwable $e) {
                // Not present → nothing to drop.
            }

            Schema::table('roles', function (Blueprint $table) use ($teamsConfig) {
                $table->unique([$teamsConfig, 'name', 'guard_name'], 'roles_team_name_guard_unique');
            });

            // Backfill existing roles to Default Tenant.
            DB::table('roles')->whereNull($teamsConfig)->update([$teamsConfig => $defaultId]);
        }

        // --- model_has_roles -------------------------------------------------
        if (Schema::hasTable('model_has_roles') && ! Schema::hasColumn('model_has_roles', $teamsConfig)) {
            Schema::table('model_has_roles', function (Blueprint $table) use ($teamsConfig) {
                $table->unsignedBigInteger($teamsConfig)->nullable()->after('model_id');
                $table->index($teamsConfig, "mhr_{$teamsConfig}_index");
            });

            DB::table('model_has_roles')->whereNull($teamsConfig)->update([$teamsConfig => $defaultId]);
        }

        // --- model_has_permissions -------------------------------------------
        if (Schema::hasTable('model_has_permissions') && ! Schema::hasColumn('model_has_permissions', $teamsConfig)) {
            Schema::table('model_has_permissions', function (Blueprint $table) use ($teamsConfig) {
                $table->unsignedBigInteger($teamsConfig)->nullable()->after('model_id');
                $table->index($teamsConfig, "mhp_{$teamsConfig}_index");
            });

            DB::table('model_has_permissions')->whereNull($teamsConfig)->update([$teamsConfig => $defaultId]);
        }
    }

    public function down(): void
    {
        $teamsConfig = config('permission.column_names.team_foreign_key', 'tenant_id');

        if (Schema::hasTable('roles') && Schema::hasColumn('roles', $teamsConfig)) {
            Schema::table('roles', function (Blueprint $table) use ($teamsConfig) {
                try { $table->dropUnique('roles_team_name_guard_unique'); } catch (\Throwable $e) {}
                try { $table->dropIndex("roles_{$teamsConfig}_index"); }    catch (\Throwable $e) {}
                $table->dropColumn($teamsConfig);
            });

            // Restore Spatie's default unique index.
            try {
                Schema::table('roles', function (Blueprint $table) {
                    $table->unique(['name', 'guard_name'], 'roles_name_guard_name_unique');
                });
            } catch (\Throwable $e) {}
        }

        if (Schema::hasTable('model_has_roles') && Schema::hasColumn('model_has_roles', $teamsConfig)) {
            Schema::table('model_has_roles', function (Blueprint $table) use ($teamsConfig) {
                try { $table->dropIndex("mhr_{$teamsConfig}_index"); } catch (\Throwable $e) {}
                $table->dropColumn($teamsConfig);
            });
        }

        if (Schema::hasTable('model_has_permissions') && Schema::hasColumn('model_has_permissions', $teamsConfig)) {
            Schema::table('model_has_permissions', function (Blueprint $table) use ($teamsConfig) {
                try { $table->dropIndex("mhp_{$teamsConfig}_index"); } catch (\Throwable $e) {}
                $table->dropColumn($teamsConfig);
            });
        }
    }
};
