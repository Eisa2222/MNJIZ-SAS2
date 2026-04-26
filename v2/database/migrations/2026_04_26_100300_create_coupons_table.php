<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * V2 Central — Coupons catalogue.
 *
 * Spec line 134 + lines 391-417 (full coupon spec):
 *   - applicable_plans json nullable (null = all plans)
 *   - billing_cycles json nullable (null = both monthly + yearly)
 *   - uses_count atomic increment (not $coupon->uses_count++)
 *   - code IMMUTABLE after creation (enforced at controller layer)
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->enum('type', ['percentage', 'fixed']);
            $table->decimal('value', 10, 2);
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('uses_count')->default(0);
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->json('applicable_plans')->nullable();   // null = ALL plans
            $table->json('billing_cycles')->nullable();      // null = both
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()
                  ->constrained('super_admins')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
