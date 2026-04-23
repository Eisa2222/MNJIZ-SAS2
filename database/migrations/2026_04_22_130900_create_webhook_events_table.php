<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotency + audit log for every webhook event received.
 *
 * The UNIQUE on (gateway, event_id) prevents duplicate processing when a
 * gateway retries. `processed_at` nullable → still processing; set on success.
 *
 * tenant_id nullable because gateway events aren't always attributable before
 * correlation (e.g. failed payments with no metadata). Populated when known.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->enum('gateway', ['moyasar', 'manual', 'stripe', 'hyperpay']);
            $table->string('event_id')->comment('gateway-provided event identifier');
            $table->string('event_type', 64)->index();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();

            $table->json('payload');
            $table->string('signature', 512)->nullable();
            $table->boolean('verified')->default(false);

            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('retry_count')->default(0);

            $table->timestamps();

            $table->unique(['gateway', 'event_id']);
            $table->index(['gateway', 'event_type', 'processed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};
