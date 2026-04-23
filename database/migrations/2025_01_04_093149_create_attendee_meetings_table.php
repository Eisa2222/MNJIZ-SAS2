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
        Schema::create('attendee_meetings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_id'); // ارتباط بالاجتماع
            $table->string('email'); // البريد الإلكتروني للمدعو
            $table->enum('type', ['employees', 'customers', 'opponents', 'additional']); // نوع المدعو
            $table->timestamps();

            // إضافة مفتاح أجنبي لربط الجدول بالاجتماعات
            $table->foreign('meeting_id')->references('id')->on('meeting_notes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendee_meetings');
    }
};
