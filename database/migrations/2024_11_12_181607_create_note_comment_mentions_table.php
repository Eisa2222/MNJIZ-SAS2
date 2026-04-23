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
        Schema::create('note_comment_mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_comment_id')->constrained('lawsuit_note_replies')->onDelete('cascade');
            $table->foreignId('mentioner_user_id')->constrained('users')->onDelete('cascade'); // المستخدم الذي أضاف المنشن
            $table->foreignId('mentioned_user_id')->constrained('users')->onDelete('cascade'); // المستخدم المذكور
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_comment_mentions');
    }
};
