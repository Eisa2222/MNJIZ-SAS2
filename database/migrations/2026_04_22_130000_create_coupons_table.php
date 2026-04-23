<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->text('description')->nullable();

            $table->enum('type', ['percentage', 'fixed']);
            $table->decimal('value', 12, 2);
            $table->string('currency', 3)->default('SAR'); // for fixed-amount coupons

            $table->enum('duration', ['once', 'repeating', 'forever'])->default('once');
            $table->unsignedInteger('duration_in_months')->nullable();

            $table->enum('applies_to', ['any', 'specific_plans'])->default('any');

            $table->decimal('min_amount', 12, 2)->nullable();
            $table->unsignedInteger('max_redemptions')->nullable();
            $table->unsignedInteger('redemptions_count')->default(0);
            $table->timestamp('redeem_by')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();

            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
