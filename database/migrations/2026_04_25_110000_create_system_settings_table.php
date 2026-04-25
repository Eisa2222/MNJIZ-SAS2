<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase C — central key/value store for SaaS-wide configuration.
 *
 * The reference SaaS prompt mandates a single `system_settings` table.
 * The legacy stack already has THREE settings systems in production:
 *
 *   - `settings`         — Phase 0 wide-column singleton, 49 active call-sites
 *   - `central_settings` — Phase 3 platform key/value store
 *   - `tenant_settings`  — Phase 3 per-tenant key/value store
 *
 * Path C does NOT delete any of those. This migration adds the
 * spec-compliant `system_settings` table alongside, with a SystemSettingsService
 * bridge that reads from system_settings first and falls back to central_settings
 * for unknown keys (plus dual-writes the keys that exist in central_settings).
 *
 * Central-only table — no `tenant_id`. Per-tenant settings keep living in
 * `tenant_settings`.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('system_settings')) {
            return;
        }

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 191)->unique();
            $table->longText('value')->nullable();
            $table->string('group', 64)->index();
            $table->string('label')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->string('cast', 16)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
