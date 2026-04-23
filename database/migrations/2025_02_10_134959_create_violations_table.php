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
        Schema::create('violations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');

            $table->foreignId('settings_violation_id')->constrained('settings_violations')->onDelete('cascade');

            $table->dateTime('violation_date'); // تاريخ وقوع المخالفة
            $table->unsignedTinyInteger('occurrence'); // رقم التكرار (1 للمرة الأولى، 2 للمرة الثانية، إلخ)
            $table->string('reference_number')->nullable(); // رقم مرجعي للمخالفة

            $table->string('penalty_text');  // النص الوصفي للعقوبة المحسوبة

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'under_appeal',
                'appeal_submitted',
                'cancelled',
                'executed'
            ])->default('pending');

            // التظلم
            $table->boolean('is_appealable')->default(false);

            $table->integer('appeal_days')->default(0);

            // ملاحظات
            $table->text('notes')->nullable();

            $table->foreignId('reviewed_by')->nullable()->constrained('employees')->onDelete('cascade');
            $table->timestamp('reviewed_at')->nullable();

            $table->foreignId('created_by')->constrained('employees')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('employees')->onDelete('cascade');

            // للربط مع الحضور 
            $table->foreignId('related_attendance_id')->nullable()->constrained('attendances')->onDelete('cascade')->onUpdate('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_violations');
    }
};
