<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase F — secure password-setup tokens for newly-provisioned tenants.
 *
 * The Phase 9 / Phase E flows used to email a plaintext-set-by-form
 * password (signup) or auto-create a user with a random password
 * (checkout). Phase F replaces both with a 48h signed setup link:
 *
 *   1. CreateTenantJob inserts a row here with a HASHED token (we
 *      never persist the plaintext — the only copy goes into the
 *      signed URL emailed to the user).
 *   2. The user clicks the URL → `tenant.password.setup` route validates
 *      the URL signature (48h expiry) AND the token by hash compare.
 *   3. After the user picks a password the row is deleted so the link
 *      is one-shot.
 *
 * Kept separate from Laravel's stock `password_reset_tokens` table to
 * avoid coupling with the 5-minute reset expiry policy and to keep
 * resets vs. setup logically distinct in audit logs.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('tenant_password_setup_tokens')) {
            return;
        }

        Schema::create('tenant_password_setup_tokens', function (Blueprint $table) {
            $table->string('email', 191)->primary();
            $table->string('token', 191);   // hashed (sha256), never plaintext
            $table->timestamp('created_at')->useCurrent();

            $table->index(['email', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_password_setup_tokens');
    }
};
