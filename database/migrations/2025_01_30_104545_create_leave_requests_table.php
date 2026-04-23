<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * تشغيل الميغريشن.
     */
    public function up()
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained('settings_leave_types')->onDelete('cascade');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('days_count', 8, 1)->nullable();
            $table->dateTime('start_datetime')->nullable()->comment('بداية الإجازة (للنصف يوم)');
            $table->dateTime('end_datetime')->nullable()->comment('نهاية الإجازة (للنصف يوم)');
            $table->integer('hours_count')->nullable()->comment('عدد الساعات المحسوبة لنصف اليوم');
            $table->enum('status', ['pending', 'approved', 'rejected', 'closed'])->default('pending');
            $table->foreignId('created_by')->constrained('employees')->onDelete('cascade')->comment('الشخص الذي أنشأ الطلب');
            $table->foreignId('updated_by')->nullable()->constrained('employees')->onDelete('cascade')->comment('الشخص الذي قام بتعديل الطلب');
            $table->text('reason')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * التراجع عن الميغريشن.
     */
    public function down()
    {
        Schema::dropIfExists('leave_requests');
    }
};
