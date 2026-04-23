<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spatie activity_log is shared across all tenants today. We add tenant_id so
 * every row records which tenant it belongs to. TenancyServiceProvider stamps
 * the value automatically via Activity::creating().
 *
 * Nullable because central/admin actions have no tenant. Historical rows are
 * backfilled to Default Tenant.
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('activity_log')) {
            return;
        }

        Schema::table('activity_log', function (Blueprint $table) {
            if (Schema::hasColumn('activity_log', 'tenant_id')) {
                return;
            }

            $table->foreignId('tenant_id')
                ->nullable()
                ->after('id')
                ->constrained('tenants')
                ->nullOnDelete();

            $table->index(['tenant_id', 'created_at']);
        });

        $defaultId = (int) env('TENANCY_DEFAULT_TENANT_ID', 1);
        DB::table('activity_log')->whereNull('tenant_id')->update(['tenant_id' => $defaultId]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('activity_log')) {
            return;
        }

        Schema::table('activity_log', function (Blueprint $table) {
            if (! Schema::hasColumn('activity_log', 'tenant_id')) {
                return;
            }

            try { $table->dropIndex(['tenant_id', 'created_at']); } catch (\Throwable $e) {}

            $table->dropConstrainedForeignId('tenant_id');
        });
    }
};
