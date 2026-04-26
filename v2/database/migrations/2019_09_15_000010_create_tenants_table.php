<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();

            // ─── Spec-mandated custom columns (line 130) ─────────────
            $table->string('company_name');
            $table->string('owner_name');
            $table->string('owner_email');
            $table->string('owner_phone')->nullable();
            $table->string('logo')->nullable();
            $table->string('timezone', 64)->default('Asia/Riyadh');
            $table->string('language', 5)->default('ar');
            $table->enum('status', ['active', 'suspended', 'pending'])
                  ->default('active')
                  ->index();
            $table->json('settings')->nullable();

            $table->timestamps();
            $table->json('data')->nullable();   // stancl internal blob

            $table->index('owner_email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
