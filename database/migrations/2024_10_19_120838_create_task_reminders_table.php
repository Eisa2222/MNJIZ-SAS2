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
        Schema::create('task_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('reminder_type', ['email', 'popup']);
            $table->integer('value'); // الرقم (مثل 10 أو 20)
            $table->enum('unit', ['hours', 'minutes', 'days', 'weeks']);
            $table->timestamps();
            // SoftDeletes لحذف التذكير بشكل مؤقت
            // $table->softDeletes();
            $table->integer('repeat_count')->default(0);
            $table->boolean('is_dismissed')->default(false);
            // Foreign key
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_reminders');
    }
};