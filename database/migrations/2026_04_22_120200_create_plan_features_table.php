<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivot: which features a given plan grants, and at what value.
 *
 *   value semantics:
 *     - Boolean  →  "1" | "0"
 *     - Limit    →  "<integer>" | "__unlimited__"
 *     - Metered  →  "<integer>" | "__unlimited__"    (per reset_period)
 *
 * is_highlighted: surfaces this feature on the pricing/public plan card.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->foreignId('feature_id')->constrained('features')->cascadeOnDelete();
            $table->string('value')->nullable();
            $table->boolean('is_highlighted')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['plan_id', 'feature_id']);
            $table->index(['plan_id', 'is_highlighted']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_features');
    }
};
