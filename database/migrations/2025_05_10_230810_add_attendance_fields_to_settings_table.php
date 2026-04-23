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
        Schema::table('settings', function (Blueprint $table) {
            // إضافة حقول جديدة خاصة بالحضور والانصراف
            $table->time('attendance_start_time')->nullable()->comment('وقت بداية تسجيل الحضور');
            $table->time('attendance_end_time')->nullable()->comment('وقت نهاية تسجيل الحضور');
            $table->time('departure_start_time')->nullable()->comment('وقت بداية تسجيل الانصراف');
            $table->boolean('attendance_system_locked')->default(false)->comment('قفل نظام الحضور والانصراف');

            // إضافة حقل تفعيل حسم التأمينات
            $table->boolean('insurance_deduction')->default(false)->comment('تفعيل حسم التأمينات');
            $table->decimal('insurance_percentage', 5, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // حذف الحقول عند التراجع عن الهجرة
            $table->dropColumn([
                'attendance_start_time',
                'attendance_end_time',
                'departure_start_time',
                'attendance_system_locked',
                'insurance_deduction',
                'insurance_percentage'
            ]);
        });
    }
};
