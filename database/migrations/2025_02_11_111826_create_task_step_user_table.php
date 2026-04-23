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
        Schema::create('task_step_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_step_id')->constrained('task_steps')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->timestamp('assigned_at')->nullable(); // تاريخ ووقت التعيين
            $table->string('user_attachment')->nullable(); // مرفق يُرفَق من قبل المستخدم أثناء التنفيذ (اختياري)

            // حقول تكامل Microsoft Graph لكل عملية تعيين
            $table->string('graph_list_id')->nullable();
            $table->string('graph_task_id')->nullable();
            $table->string('graph_event_id')->nullable();

            $table->timestamps();

            // لضمان عدم تكرار العلاقة لنفس المهمة ونفس المستخدم
            $table->unique(['task_step_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_step_users');
    }
};
