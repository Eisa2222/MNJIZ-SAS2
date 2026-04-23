<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'tenant_id')) {
                return;
            }

            $table->foreignId('tenant_id')
                ->nullable()
                ->after('id')
                ->constrained('tenants')
                ->nullOnDelete();

            $table->index(['tenant_id', 'status_id']);
            $table->index(['tenant_id', 'name']);
        });

        $defaultId = (int) env('TENANCY_DEFAULT_TENANT_ID', 1);
        DB::table('customers')->whereNull('tenant_id')->update(['tenant_id' => $defaultId]);
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'tenant_id')) {
                return;
            }

            try { $table->dropIndex(['tenant_id', 'status_id']); } catch (\Throwable $e) {}
            try { $table->dropIndex(['tenant_id', 'name']); }      catch (\Throwable $e) {}

            $table->dropConstrainedForeignId('tenant_id');
        });
    }
};
