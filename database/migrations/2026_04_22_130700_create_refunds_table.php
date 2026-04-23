<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->restrictOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();

            $table->enum('status', ['pending', 'succeeded', 'failed']);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('SAR');

            $table->string('reason')->nullable();
            $table->string('gateway_refund_id')->nullable()->unique();
            $table->timestamp('refunded_at')->nullable();
            $table->text('failure_reason')->nullable();

            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
