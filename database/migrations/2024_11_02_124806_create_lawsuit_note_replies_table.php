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
        Schema::create('lawsuit_note_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawsuit_note_id')->constrained('lawsuit_notes')->onDelete('cascade'); // ربط الرد بالملاحظة
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // المستخدم الذي قام بالرد
            $table->text('reply_text'); // نص الرد
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lawsuit_note_replies');
    }
};