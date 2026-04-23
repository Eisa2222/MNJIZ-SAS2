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
        Schema::create('assigned_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('session_id')
                  ->constrained('sessions')
                  ->onDelete('cascade');

            $table->foreignId('assigned_to') //المكلف
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->foreignId('user_added_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->timestamps();

            // إضافة فهرس فريد لمنع التكرار
            $table->unique(['session_id', 'assigned_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assigned_sessions');
    }
};
