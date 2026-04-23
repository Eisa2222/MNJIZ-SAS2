<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('billing_invoices')->nullOnDelete();

            $table->enum('status', [
                'pending', 'authorized', 'captured', 'failed',
                'refunded', 'partially_refunded',
            ])->index();

            $table->decimal('amount',          12, 2);
            $table->decimal('amount_refunded', 12, 2)->default(0);
            $table->string('currency', 3)->default('SAR');

            $table->enum('gateway', ['moyasar', 'manual', 'stripe', 'hyperpay']);
            $table->string('gateway_payment_id')->nullable()->unique();

            // Non-sensitive card metadata — never the PAN.
            $table->string('card_last4', 4)->nullable();
            $table->string('card_brand', 32)->nullable();
            $table->string('source_type', 32)->nullable(); // mada | visa | mastercard | apple_pay | stc_pay

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();

            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['gateway', 'gateway_payment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
