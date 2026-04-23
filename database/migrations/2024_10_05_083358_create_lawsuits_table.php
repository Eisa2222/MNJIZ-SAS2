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

        Schema::create('lawsuits', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // الاسم
            $table->string('lawsuit_number')->unique(); // رقم الدعوى


            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade'); // المشروع

            $table->foreignId('main_courts_id')->constrained('settings_main_courts')->onDelete('cascade'); //المحكمة الام
            $table->foreignId('regions_id')->constrained('settings_regions')->onDelete('cascade'); // المدينة

            $table->text('circle')->nullable(); // الدائرة

            $table->foreignId('category_id')->constrained('settings_categories')->onDelete('cascade'); //التصنيف الرئيسي المرتبط
            $table->foreignId('subcategory_id')->constrained('settings_subcategories')->onDelete('cascade'); //التصنيف الفرعي المرتبط
            $table->foreignId('lawsuit_type_id')->constrained('settings_lawsuits_types')->onDelete('cascade'); //نوع الدعوى المرتبط

            $table->text('lawsuit_subject')->nullable(); // موضوع الدعوى
            $table->text('plaintiff_requests')->nullable(); // طلبات المدعي
            $table->text('lawsuit_proofs')->nullable(); // أسانيد الدعوى

            // مذكرة الدفاع الأولى
            $table->text('defense_memo')->nullable();
            $table->string('defense_memo_attachment')->nullable();


            // الأحكام
            $table->text('judgment')->nullable();
            $table->string('judgment_attachment')->nullable();

            // الطلبات
            $table->text('request')->nullable();
            $table->string('request_attachment')->nullable();

            // القرارات
            $table->text('decision')->nullable();
            $table->string('decision_attachment')->nullable();
            $table->enum('lawsuit_status', ['active', 'inactive', 'pending', 'rejected'])->default('active');

            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('cascade');


            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lawsuits');
    }
};
