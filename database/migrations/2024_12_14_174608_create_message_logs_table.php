<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageLogsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('message_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id'); // معرف المرسل (المستخدم)
            $table->text('message_text'); // نص الرسالة
            $table->string('platform'); // منصة الرسالة (SMS، Email، WhatsApp)
            $table->json('recipients'); // المستلمون بتنسيق JSON يحتوي على النوع والمعرف
            $table->timestamps();

            // علاقات المفتاح الخارجي
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_logs');
    }
}