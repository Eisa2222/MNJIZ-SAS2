<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // البيانات الشخصية
            $table->string('name');
            $table->unsignedBigInteger('nationality')->nullable(); // استخدم nationality بدلاً من country_id
            $table->foreign('nationality')->references('id')->on('settings_countries')->onDelete('cascade');
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('id_number')->unique();
            $table->string('personal_email')->nullable();
            $table->string('work_email')->unique();
            $table->text('mobile')->nullable();
            $table->string('address')->nullable();
            $table->date('birth_date')->nullable();

            // معلومات الوظيفة
            // إضافة حقل 'hr_status_id' كمفتاح أجنبي
            $table->unsignedBigInteger('hr_status_id')->nullable();
            // إضافة القيد الخارجي
            $table->foreign('hr_status_id')->references('id')->on('settings_hr_statuses')->onDelete('cascade');

            $table->string('job_title')->nullable();

            $table->enum('license_type', ['trainee_lawyer', 'lawyer', 'no_license'])->nullable();



            $table->enum('insurance_status', ['added', 'informally_added', 'excluded', 'not_registered']);
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->date('work_license_end_date')->nullable();

            $table->date('training_end_date')->nullable();

            // الرواتب والبدلات
            $table->decimal('basic_salary', 10, 2)->nullable();
            $table->decimal('transportation_allowance', 10, 2)->nullable();
            $table->decimal('housing_allowance', 10, 2)->nullable();
            $table->decimal('other_allowances', 10, 2)->nullable();

            // معلومات أخرى
            $table->string('training_number')->nullable(); //رقم التدريب
            $table->string('national_number')->nullable(); // الرقم الوطني
            $table->enum('qualification_degree', ['secondary', 'diploma', 'bachelor', 'master', 'doctorate'])->nullable();
            $table->text('bio')->nullable();
            $table->integer('vacation_balance')->nullable();  // رصيد الاجازات
            $table->string('business_card')->nullable(); //بطاقة العمل
            $table->enum('knowledge_area', ['inheritance_and_wills', 'intellectual_property', 'real_estate', 'endowments', 'gov_consulting_and_legislation'])->nullable();

            // المرفقات
            $table->string('profile_picture')->nullable();
            // الصورة الخاصة بخلفية الملف الشخصي
            $table->string('background_image')->nullable();
            // الصورة الخاصة بخلفية الملف الشخصي
            $table->string('resume')->nullable();
            $table->string('qualification_certificate')->nullable();
            $table->string('contract_attachment')->nullable();
            $table->string('id_attachment')->nullable();
            $table->string('bank_account_attachment')->nullable();
            $table->string('national_address_attachment')->nullable();
            $table->string('signature')->nullable();

            // مرفقات إضافية
            $table->json('additional_attachments')->nullable(); // حقل لتخزين المرفقات الإضافية

            // بيانات الدخول للنظام
            $table->unsignedBigInteger('user_id')->nullable(); // لربط الموظف بالمستخدم
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // تصنيف الموارد البشرية
            $table->unsignedBigInteger('hr_classification_id')->nullable();
            $table->foreign('hr_classification_id')->references('id')->on('settings_h_r_classifications')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes(); // استخدام Soft Deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
