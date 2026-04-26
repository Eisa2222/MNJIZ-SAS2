<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * V2 Central — Per-redemption coupon audit trail.
 * Spec line 135.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('coupon_uses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->string('tenant_id');
            // Note: subscription_id FK is added by a later migration once
            // the subscriptions table exists (avoids circular FK between
            // coupons → subscriptions → coupons).
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->decimal('discount_amount', 10, 2);
            $table->timestamp('used_at');
            $table->timestamps();

            $table->index(['coupon_id', 'used_at']);
            $table->index('subscription_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_uses');
    }
};
