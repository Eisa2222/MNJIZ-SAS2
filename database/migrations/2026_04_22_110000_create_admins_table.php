<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Platform-level super-admin accounts. NEVER scoped by tenant.
 *
 * Lives alongside users/ — the two are strictly separate:
 *   - users.*     → tenant-owned accounts (firm staff, employees)
 *   - admins.*    → platform operators (our SaaS team)
 *
 * Guard: 'admin' (session-based, separate from 'web').
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['super_admin', 'admin', 'support'])->default('admin')->index();
            $table->enum('status', ['active', 'suspended'])->default('active')->index();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
