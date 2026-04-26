<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * V2 Central — Subscription plans catalogue.
 *
 * Spec ref: line 129
 *   id, name, slug, description, price_monthly, price_yearly, currency,
 *   trial_days, max_users, max_storage_gb, features json, limits json,
 *   is_active, is_featured, sort_order, badge_text, badge_color
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_yearly', 10, 2)->default(0);
            $table->string('currency', 3)->default('SAR');
            $table->unsignedInteger('trial_days')->default(0);
            $table->unsignedInteger('max_users')->nullable();
            $table->unsignedInteger('max_storage_gb')->nullable();
            $table->json('features')->nullable();
            $table->json('limits')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('badge_text')->nullable();
            $table->string('badge_color', 32)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
