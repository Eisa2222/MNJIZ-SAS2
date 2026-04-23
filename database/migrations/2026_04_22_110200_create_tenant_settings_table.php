<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-tenant settings. Replaces (gradually) the legacy single-row `settings`
 * table. Qoyod keys, Microsoft tokens, SMS creds, work hours, WPS config,
 * BioStation device data — all move here in Phase 7 data migration.
 *
 * Uses (tenant_id, key) composite unique so a tenant cannot collide on a key
 * but two tenants can share the same key name.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('key');
            $table->longText('value')->nullable();
            $table->string('group', 64)->default('general')->index();
            $table->boolean('is_encrypted')->default(false);
            $table->string('cast', 16)->default('string');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'key'], 'tenant_settings_tenant_key_unique');
            $table->index(['tenant_id', 'group']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');
    }
};
