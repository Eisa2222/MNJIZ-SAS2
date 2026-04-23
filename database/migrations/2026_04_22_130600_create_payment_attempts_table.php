<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every gateway round-trip logged — retries, 3DS redirects, webhook updates.
 * Useful for forensics and fraud review. NEVER deleted.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->string('status', 32);                 // free-form; mirrors gateway response
            $table->string('gateway_event_id')->nullable()->index();
            $table->text('error_message')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('attempted_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
    }
};
