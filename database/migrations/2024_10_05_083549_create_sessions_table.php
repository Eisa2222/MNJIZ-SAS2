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
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_name'); // اسم الجلسة

            $table->date('session_date'); // التاريخ الميلادي مع الوقت
            $table->time('session_time'); // إضافة الحقل session_time لتخزين الوقت

            // علاقات مع جداول المشاريع والدعاوى
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade'); // المشروع
            $table->foreignId('lawsuit_id')->constrained('lawsuits')->onDelete('cascade'); // الدعوى


            $table->text('notes')->nullable(); //  الملاحظات

            // خيارات التقرير الإجمالي
            $table->enum('summary_report_status', [
                // 'مرفوعة للنظر',
                // 'لا يوجد',
                // 'شطب الدعوى',
                // 'الصلح',
                // 'وقف السير',
                // 'تأجيل',
                // 'حفظ الدعوى',
                // 'حكم موضوعي',
                // 'تأييد الحكم',
                // 'حكم شكلي'

                'raised_for_consideration',
                'none',
                'case_striking_off',
                'settlement',
                'stay_of_proceedings',
                'postponement',
                'case_preservation',
                'substantive_ruling',
                'formal_ruling',
                'ruling_affirmation'
            ])->default('none');

            $table->text('execution_minutes')->nullable(); // دقائق التنفيذ


            $table->date('expected_execution_date')->nullable(); // التاريخ المتوقع للتنفيذ
            $table->timestamp('reminder_sent_at')->nullable();

            $table->timestamp('objection_reminder_sent_at')->nullable(); // للاعتراضات
            // خيارات إرسال التقرير
            $table->enum('report_sending_method', [
                'مرسل بريديا',
                'مرسل هاتفيا',
                'مرسل عبر الواتساب',
                'مرسل بوسيلة اخرى',
                'غير مرسل',
            ])->default('غير مرسل');

            $table->foreignId('entity_ranks_id')->constrained('settings_entity_ranks')->onDelete('cascade'); //درجة الجهة

            $table->text('session_control_attached')->nullable();
            $table->text('rule_attached')->nullable();


            $table->enum('session_status', ['active', 'inactive', 'pending_session_control'])->default('active');

            $table->foreignId('session_type')->nullable()->constrained('settings_session_types')->onDelete('cascade'); //
            $table->foreignId('rule_type')->nullable()->constrained('settings_type_rulings')->onDelete('cascade'); //


            $table->date('last_objection_deadline')->nullable();

            $table->string('objection_status')->nullable();

            $table->string('execution_format')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('employees')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('employees')->onDelete('cascade');


            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
