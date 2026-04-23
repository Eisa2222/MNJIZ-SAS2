<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('openai_api_key')->nullable();
            $table->string('openai_model')->nullable();
            $table->boolean('chatgpt_enabled')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('openai_api_key');
            $table->dropColumn('openai_model');
            $table->dropColumn('chatgpt_enabled');
        });
    }
};