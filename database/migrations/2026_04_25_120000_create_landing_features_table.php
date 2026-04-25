<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase D — landing page Features blocks (CMS-driven).
 *
 * Phase 9 shipped a hardcoded 6-feature grid in `marketing/landing.blade.php`.
 * Phase D moves them into the DB so the Super Admin can add/edit/reorder
 * marketing copy without a deploy.
 *
 * Central-only — no `tenant_id`. Features are part of the public-facing
 * marketing surface and apply to every visitor.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('landing_features')) {
            return;
        }

        Schema::create('landing_features', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('description');
            $table->string('icon', 64)->nullable();   // emoji or icon class
            $table->string('image', 500)->nullable(); // optional URL or path
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_features');
    }
};
