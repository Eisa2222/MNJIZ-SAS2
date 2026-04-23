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
        Schema::create('task_step_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_step_id');
            $table->unsignedBigInteger('user_id')->nullable(); // المستخدم الذي قام بالحدث (يمكن أن يكون المسؤول أو المعني)
            $table->string('event_type'); // مثل "complete", "approve", "reject"
            $table->text('message')->nullable(); // رسالة توضيحية (مثل سبب الرفض)
            $table->timestamps();

            // المفاتيح الخارجية (إذا كانت الجداول موجودة)
            $table->foreign('task_step_id')->references('id')->on('task_steps')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_step_events');
    }
};
