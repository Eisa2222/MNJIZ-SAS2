<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * V2 Central — Operator-managed runtime settings (Moyasar keys, mail
 * config, hero copy, trial knobs, notifications). Read via
 * SystemSetting::get('key', $default), write via ::set / setMany.
 *
 * Sensitive values (moyasar_secret_key, mail_password, moyasar_webhook_secret)
 * are stored encrypted via Laravel's `encrypt()`.
 *
 * Spec lines 233-318.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 191)->unique();
            $table->text('value')->nullable();
            $table->string('group', 64)->default('general')->index();
            $table->string('label')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
