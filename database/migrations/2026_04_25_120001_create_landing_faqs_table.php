<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase D — landing page FAQ entries (CMS-driven).
 *
 * Phase 9 shipped 6 hardcoded Q/A pairs inside the landing view. Phase D
 * moves them into the DB so the Super Admin can curate questions over
 * time. The `answer` column is `text` so HTML can be rendered (sanitised
 * on output via Blade `{!! !!}` only after passing through a safe-list
 * filter — for now FormRequest validation enforces a max length and the
 * view escapes by default).
 *
 * Central-only — no `tenant_id`.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('landing_faqs')) {
            return;
        }

        Schema::create('landing_faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question', 500);
            $table->text('answer');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_faqs');
    }
};
