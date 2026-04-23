<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One subscription per tenant at any given time (latest is authoritative).
 * Old canceled/expired rows are kept for history — query with
 * `where('status', SubscriptionStatus::Active)` for the active one.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->restrictOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();

            $table->enum('status', [
                'trialing', 'active', 'past_due', 'paused', 'canceled', 'expired',
            ])->index();

            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->string('currency', 3)->default('SAR');
            $table->enum('gateway', ['moyasar', 'manual', 'stripe', 'hyperpay'])->default('moyasar');
            $table->string('gateway_subscription_id')->nullable()->index();

            // Lifecycle timestamps — all nullable to model the full state machine.
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_started_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable()->index();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('ends_at')->nullable();       // when entitlement stops (after cancel, expire)
            $table->timestamp('grace_ends_at')->nullable(); // past_due grace window

            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['status', 'current_period_ends_at']);
            $table->index(['status', 'grace_ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
