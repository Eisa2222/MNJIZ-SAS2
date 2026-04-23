<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-tenant counter for metered features.
 *
 * One row per (tenant_id, feature_key). `used` is the CURRENT-period consumption.
 * When reset_at passes, TrackUsageAction resets it to 0 and recalculates reset_at
 * from feature.reset_period.
 *
 * feature_key is a denormalized reference (not FK) so renaming a feature doesn't
 * cascade-destroy history. Lookups always use the key.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('tenant_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('feature_key', 100);
            $table->unsignedBigInteger('used')->default(0);
            $table->timestamp('period_started_at')->nullable();
            $table->timestamp('reset_at')->nullable();      // NULL = lifetime (never resets)
            $table->timestamp('last_tracked_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'feature_key'], 'tenant_usages_tenant_feature_unique');
            $table->index(['tenant_id', 'reset_at']);
            $table->index('feature_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_usages');
    }
};
