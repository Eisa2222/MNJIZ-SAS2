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
        Schema::create('wps_payroll_details', function (Blueprint $table) {
            $table->id();

            // ربط بجدول دفعات الرواتب
            $table->foreignId('wps_payroll_id')->constrained('wps_payrolls')->onDelete('cascade');

            // ربط بجدول الموظفين
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');

            $table->enum('status', [
                'not_requested', // لم يتم طلب مراجعة
                'pending', //في انتظار الاعتماد
                'reviewed_approved', // تم تعديل الراتب بعد المراجعة
                'reviewed_rejected', // تم رفض التعديل  بعد المراجعة
            ])->default('not_requested');

            // مكونات الراتب
            $table->decimal('basic',     10, 2);
            $table->decimal('transport', 10, 2)->default(0);
            $table->decimal('housing',   10, 2)->default(0);
            $table->decimal('other',     10, 2)->default(0);

            $table->decimal('insurance',     10, 2)->default(0);

            // الاستقطاعات والصافي
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('net',        12, 2);

            // حوافز
            $table->decimal('incentives', 10, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wps_payroll_details');
    }
};
