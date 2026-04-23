<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds tenant_id to `users` and backfills every existing row to the Default
 * Tenant so the legacy /employees/* routes keep working during Phase 2.
 *
 * Column stays nullable until Phase 6 flips it to NOT NULL.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'tenant_id')) {
                return;
            }

            $table->foreignId('tenant_id')
                ->nullable()
                ->after('id')
                ->constrained('tenants')
                ->nullOnDelete();

            $table->index(['tenant_id', 'email']);
            $table->index(['tenant_id', 'status']);
        });

        // Backfill pre-existing rows to Default Tenant.
        $defaultId = (int) env('TENANCY_DEFAULT_TENANT_ID', 1);
        DB::table('users')->whereNull('tenant_id')->update(['tenant_id' => $defaultId]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'tenant_id')) {
                return;
            }

            // Drop indexes first (names derived by Laravel's default naming).
            try { $table->dropIndex(['tenant_id', 'email']); }  catch (\Throwable $e) {}
            try { $table->dropIndex(['tenant_id', 'status']); } catch (\Throwable $e) {}

            $table->dropConstrainedForeignId('tenant_id');
        });
    }
};
