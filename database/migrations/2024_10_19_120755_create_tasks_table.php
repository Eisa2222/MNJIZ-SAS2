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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->string('task_name');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->longText('description')->nullable();

            $table->enum(
                'task_field',
                [
                    'offer',
                    'contract',
                    'projects',
                    'lawsuits',
                    'sessions',
                    'power_of_attorney',
                    'marketing',
                    'renewals',
                    'sales',
                    'other',

                    'clearance_certificate',
                    'advance',
                    'reward',
                    'deduction',
                    'content',
                    'custody',
                    'leave',
                    'wps',
                ]
            )->default('other');


            // للربط مع المجال
            $table->foreignId('offer_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('contract_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('lawsuit_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('session_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('power_of_attorney_id')->nullable()->constrained()->onDelete('cascade');

            // حالة المهمة
            $table->enum('status', [
                'pending',          // قيد الانتظار
                'in_progress',      // قيد التنفيذ
                'completed',        // مكتملة
                'cancel_completion', // تم الغاء الاكمال
                'returned'              // تم ارجاع المهمة
            ])->default('pending');


            $table->date('due_date'); // تاريخ الاستحقاق
            $table->time('due_time'); // زمن الاستحقاق


            // تواريخ البدء والانتهاء
            $table->timestamp('task_start_date')->nullable();    // تاريخ بدء العمل على المهمة
            $table->timestamp('task_end_date')->nullable();      // تاريخ إكمال المهمة


            // مرفق المهمة
            $table->string('attachment')->nullable();
            // نوع المهمة في المشروع
            $table->string('type_task')->nullable();



            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('cascade');

            $table->foreignId('completed_by')->nullable()->constrained('users')->onDelete('cascade');

            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('cascade');


            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
