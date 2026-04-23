<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->nullable()->unique();
            $table->enum('status', ['active', 'suspended'])->default('active')->index();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Default Tenant must exist before any add_tenant_id_to_* migration runs,
        // so the subsequent backfills have something to point at.
        DB::table('tenants')->insert([
            'id'         => (int) env('TENANCY_DEFAULT_TENANT_ID', 1),
            'name'       => 'MNJIZ Default',
            'slug'       => (string) env('TENANCY_DEFAULT_TENANT_SLUG', 'default'),
            'domain'     => null,
            'status'     => 'active',
            'meta'       => json_encode([
                'is_default' => true,
                'created_by' => 'create_tenants_table migration',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
