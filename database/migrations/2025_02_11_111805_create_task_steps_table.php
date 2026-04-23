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
        Schema::create('task_steps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->string('name');

            $table->timestamp('step_start_date')->nullable();    // تاريخ بدء العمل على المهمة
            $table->timestamp('step_end_date')->nullable();      // تاريخ إكمال المهمة

            $table->text('reject_reason')->nullable();

            $table->integer('step_order');

            $table->boolean('needs_approval')->default(false);

            $table->enum('status', [
                'pending',          // قيد الانتظار
                'in_progress',      // قيد التنفيذ
                'completed',        // مكتملة
                'cancel_completion', // تم الغاء الاكمال
                'approved',         // معتمدة
                'rejected'          // مرفوضة
            ])->default('pending');



            $table->foreignId('completed_by')->nullable()->constrained('users')->onDelete('cascade');

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('cascade');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_steps');
    }
};