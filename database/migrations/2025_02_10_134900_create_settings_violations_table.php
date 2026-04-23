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
        Schema::create('settings_violations', function (Blueprint $table) {
            $table->id();
            // ربط المخالفة بتصنيفها
            $table->unsignedBigInteger('settings_violation_category_id');
            $table->foreign('settings_violation_category_id')
                ->references('id')
                ->on('settings_violation_categories')
                ->onDelete('cascade');

            $table->enum('violation_type', [
                'delay',        // تأخير
                'early_leave',  // ترك العمل المبكر
                'absence',      // غياب
                'after_hours',  // البقاء بعد انتهاء الدوام
                'other'         // اخرى 
            ])->default('other');

            // 3) وحدة ومدة التطبيق
            $table->enum('duration_unit', ['minutes', 'hours', 'days'])->nullable();
            $table->unsignedSmallInteger('duration_from')->nullable();
            $table->unsignedSmallInteger('duration_to')->nullable();

            // تفاصيل المخالفة
            $table->text('description');
            // الجزاءات لكل مرة (يمكن أن تكون نسب مئوية أو إجراءات مثل "إنذار كتابي")
            $table->string('penalty_first')->nullable();
            $table->string('penalty_second')->nullable();
            $table->string('penalty_third')->nullable();
            $table->string('penalty_fourth')->nullable();
            // ملاحظات إضافية مثل "بالإضافة إلى حسم أجر دقائق التأخر"
            $table->string('extra_deduction')->nullable();
            $table->enum('status', ['active', 'inactive']);
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings_violations');
    }
};
