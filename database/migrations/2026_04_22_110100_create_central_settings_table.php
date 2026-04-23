<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Platform-wide settings. Lives in the central domain.
 *
 *   key               value          group        is_encrypted
 *   -----------------  -------------  -----------  ------------
 *   moyasar.public_key pk_live_...    billing      1
 *   platform.support_email  ops@...   general      0
 *
 * Value is stored as TEXT (large values welcome). When is_encrypted=1 the
 * CentralSetting model encrypts/decrypts on the fly.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('central_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('group', 64)->default('general')->index();
            $table->boolean('is_encrypted')->default(false);
            $table->string('cast', 16)->default('string'); // string|int|bool|json|array
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('central_settings');
    }
};
