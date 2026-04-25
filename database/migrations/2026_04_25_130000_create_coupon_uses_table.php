<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase E — per-redemption audit trail for coupons.
 *
 * Phase 5 already tracks coupon usage atomically via the
 * `coupons.redemptions_count` integer counter. The spec wants a
 * granular `coupon_uses` table for analytics / customer-facing usage
 * lookup. This migration adds it ALONGSIDE the existing counter — both
 * are written by `CouponService::apply()` inside one DB::transaction so
 * the two sources can never drift.
 *
 * Central-only (no `tenant_id` global scope) — the column exists so we
 * can join back to the tenant that redeemed the coupon, but the table
 * is platform-wide.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('coupon_uses')) {
            return;
        }

        Schema::create('coupon_uses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('SAR');
            $table->timestamp('used_at')->useCurrent();
            $table->timestamps();

            $table->index(['coupon_id', 'used_at']);
            $table->index(['tenant_id', 'used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_uses');
    }
};
