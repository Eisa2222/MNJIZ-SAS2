<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * V2 Central — adds the FK from coupon_uses.subscription_id →
 * subscriptions.id AFTER both tables exist. Resolves the circular
 * dependency between coupons (FK to ?), subscriptions (FK to coupons),
 * and coupon_uses (FK to subscriptions).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('coupon_uses', function (Blueprint $table) {
            $table->foreign('subscription_id')
                  ->references('id')->on('subscriptions')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('coupon_uses', function (Blueprint $table) {
            $table->dropForeign(['subscription_id']);
        });
    }
};
