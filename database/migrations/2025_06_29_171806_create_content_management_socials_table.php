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
        Schema::create('content_management_socials', function (Blueprint $table) {

            // MySQL 8 rejects NULL columns in composite PRIMARY KEY — drop nullable().
            $table->foreignId('content_management_id')->constrained('content_management')->onDelete('cascade');
            $table->foreignId('social_id')->constrained('settings_socials')->onDelete('cascade');

            $table->timestamps();

            $table->primary(['content_management_id', 'social_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_management_socials');
    }
};
