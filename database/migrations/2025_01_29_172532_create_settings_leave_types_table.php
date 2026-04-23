<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsLeaveTypesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings_leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('days')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_paid');
            $table->boolean('is_deductible')->default(false)->comment('هل تخصم من الرصيد السنوي للمستخدم؟');
            $table->boolean('is_carry_forwardable')->default(false)->comment('هل تُرحّل هذه الإجازة للسنة القادمة؟');
            $table->enum('leave_unit_type', ['full_day', 'half_day'])->default('full_day')->comment('نوع الإجازة: يوم كامل أو نصف يوم');
            $table->enum('gender_applicability', ['both', 'female'])->default('both')->comment('كلا الجنسين|الإناث فقط');
            $table->unsignedInteger('min_service_years')->default(0)->comment('سنوات الخدمة الأدنى');
            $table->unsignedInteger('service_years_threshold')->default(0)->comment('عدد سنوات الخدمة التي تتغيّر بعدها أيام الإجازة');
            $table->unsignedInteger('days_after_threshold')->default(0)->comment('عدد الأيام بعد اجتياز عتبة سنوات الخدمة');
            $table->boolean('has_attachments')->default(false)->comment('هل تتطلب إرفاق مرفقات عند الطلب');
            $table->string('attachment_description')->nullable();
            $table->unsignedTinyInteger('max_requests')->nullable()->default(0)->comment('الحد الأقصى لعدد الطلبات (0=غير محدود)');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings_leave_types');
    }
}
