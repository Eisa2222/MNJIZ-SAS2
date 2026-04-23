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
        Schema::create('violation_executions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('violation_id')->constrained('violations')->onDelete('cascade');

            // نوع التنفيذ
            $table->enum('execution_type', [
                'warning',          // إنذار
                'deduction',        // خصم
                'suspension',       // إيقاف
                'dismissal',        // فصل
                'deprivation',      // حرمان (من العلاوات أو الترقيات)
                'other'
            ]);

            $table->foreignId('executed_by')->nullable()->constrained('employees')->onDelete('cascade');
            $table->timestamp('execution_date'); // تاريخ التنفيذ

            // تفاصيل الخصم
            $table->decimal('deduction_amount', 10, 4)->nullable();        // قيمة الخصم (إن وجدت)
            $table->integer('deduction_days')->nullable();                 // عدد أيام الخصم/الإيقاف
            $table->decimal('deduction_percentage', 6, 4)->nullable();     // نسبة الخصم


            // تاريخ التطبيق على المرتب
            $table->timestamp('applied_to_salary_date')->nullable();

            // ربط مع دورة الراتب
            $table->unsignedBigInteger('wps_payrolls_id')->nullable();
            $table->foreign('wps_payrolls_id')->references('id')->on('wps_payrolls')->onDelete('set null');



            $table->text('notes')->nullable();

            // مرفق
            $table->string('attachment')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violation_executions');
    }
};
