<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Named 'billing_invoices' (NOT 'invoices') — there is already a
        // legacy 'invoices' table for HR/Purchase (migration 2025_03_03_144022)
        // with a totally different schema. Prefix `billing_` disambiguates.
        Schema::create('billing_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->foreignId('tax_rate_id')->nullable()->constrained('tax_rates')->nullOnDelete();

            // Human-readable, e.g. "INV-2026-000123" — unique.
            $table->string('number', 32)->unique();

            $table->enum('status', [
                'draft', 'open', 'paid', 'uncollectible', 'void',
                'refunded_partial', 'refunded_full',
            ])->index();

            $table->decimal('subtotal',        12, 2)->default(0);
            $table->decimal('tax_amount',      12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total',           12, 2)->default(0);
            $table->decimal('amount_paid',     12, 2)->default(0);
            $table->decimal('amount_refunded', 12, 2)->default(0);
            $table->string('currency', 3)->default('SAR');

            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('voided_at')->nullable();

            $table->string('pdf_path')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['status', 'due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_invoices');
    }
};
