<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catalog of available features across ALL plans. A feature exists independent
 * of any plan — plans opt into features via plan_features.
 *
 * key     — stable, machine-readable identifier (e.g. "legal_ai.chat",
 *           "max_users", "api.calls_per_month"). Immutable once in production.
 * type    — boolean | limit | metered
 * reset_period — only meaningful for metered features.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('name');
            $table->enum('type', ['boolean', 'limit', 'metered'])->index();
            $table->enum('reset_period', ['never', 'daily', 'weekly', 'monthly', 'yearly'])
                  ->default('never');
            $table->string('unit', 32)->nullable();
            $table->text('description')->nullable();
            $table->string('group', 64)->default('general')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
