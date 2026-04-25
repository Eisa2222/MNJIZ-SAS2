<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase A — host-aware tenant resolution.
 *
 * Each tenant can have multiple domains:
 *   - one auto-generated subdomain  (e.g. acme.mnjiz.sa)
 *   - zero or more custom domains   (e.g. portal.acme.com)
 *
 * The `domain` column is globally unique — the same host cannot resolve to
 * two different tenants. `is_primary` flags the canonical host used when
 * generating absolute URLs (welcome emails, password setup links, etc.).
 *
 * `verified_at` is reserved for custom-domain ownership verification (DNS
 * challenge). Auto-generated subdomains are verified at creation time.
 *
 * Central-only table — no `tenant_id` on `tenants` itself, so the legacy
 * `tenants.domain` text column (single value) stays in place but is now
 * superseded by this many-to-one model.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('domains')) {
            return;
        }

        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete();
            $table->string('domain', 191)->unique();
            $table->boolean('is_primary')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('is_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
