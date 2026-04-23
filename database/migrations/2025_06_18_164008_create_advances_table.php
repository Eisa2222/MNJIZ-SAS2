<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('advance_number', 20)->unique();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->enum('advance_type', ['salary_deduction', 'cash']); // نوع السلفة: استقطاع من المرتب أو نقدي
            $table->decimal('amount', 15, 2);
            $table->date('advance_date');
            $table->decimal('remaining_amount', 15, 2)->default(0); // المبلغ المتبقّي بعد أي دفعات سداد
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'executed'
            ])->default('pending');
            $table->date('due_date')->nullable(); // تاريخ الاستحقاق
            $table->foreignId('created_by')->constrained('employees')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('employees')->onDelete('cascade');
            $table->string('notes', 500)->nullable(); // ملاحظات إضافية حول السلفة

            $table->timestamp('applied_to_salary_date')->nullable();
            // ربط مع دورة الراتب
            $table->foreignId('wps_payrolls_id')->nullable()->constrained('wps_payrolls')->onDelete('set null');


            $table->softDeletes();
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('advances');
    }
};