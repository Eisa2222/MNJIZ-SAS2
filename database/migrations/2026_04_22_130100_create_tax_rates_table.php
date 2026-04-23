<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name');                      // "VAT 15%"
            $table->string('display_name');              // "ضريبة القيمة المضافة"
            $table->decimal('percentage', 5, 2);         // 15.00
            $table->string('country', 2)->default('SA');
            $table->string('region')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_default')->default(false); // one flag for the "apply by default" rate
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['country', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
