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
        Schema::create('opponent_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opponent_id')->constrained('opponents')->onDelete('cascade');
            $table->string('name');
            $table->string('identity_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            // يمكنك إضافة أي حقول أخرى مطلوبة
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opponent_authorizations');
    }
};