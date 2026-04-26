<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * V2 Central — Moyasar payment ledger.
 *
 * Spec line 133:
 *   id, tenant_id, subscription_id, moyasar_payment_id, moyasar_invoice_id,
 *   amount, currency, status, payment_method, moyasar_response json,
 *   failure_message, paid_at
 *
 *   PCI safety: NEVER store full PAN, CVV, expiry. Moyasar tokenises
 *   client-side; we only persist the gateway-returned `id` + `last4` (in
 *   moyasar_response json) + the captured amount.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('moyasar_payment_id')->nullable()->unique();   // gateway primary key
            $table->string('moyasar_invoice_id')->nullable();
            $table->decimal('amount', 10, 2);                              // in SAR (NOT halalas)
            $table->string('currency', 3)->default('SAR');
            $table->enum('status', ['initiated', 'paid', 'failed', 'refunded'])
                  ->default('initiated')
                  ->index();
            $table->string('payment_method', 32)->nullable();   // creditcard / applepay / stcpay
            $table->json('moyasar_response')->nullable();
            $table->text('failure_message')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
