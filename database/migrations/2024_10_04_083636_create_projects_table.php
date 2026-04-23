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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_name'); // اسم المشروع
            $table->string('project_number')->unique(); // رقم المشروع
            // $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade'); // العميل
            $table->foreignId('manager_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->date('start_date'); // تاريخ البدء
            $table->date('end_date')->nullable(); // تاريخ الإغلاق
            // إضافة المفتاح الخارجي لعلاقة contract_id مع جدول contracts
            // إضافة حقل contract_id كمفتاح خارجي مرتبط بجدول contracts
            // $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');


            // $table->foreignId('new_status_id')->nullable()->constrained('settings_project_statues')->onDelete('cascade');
            $table->foreignId('new_status_id')->nullable()->constrained('settings_contract_statuses')->onDelete('cascade');



            $table->text('description')->nullable(); // وصف المشروع
            // $table->foreignId('opponent_id')->nullable()->constrained('opponents')->onDelete('cascade'); // الخصم
            // $table->string('claim_type')->nullable();    //نوع المطالبة
            // $table->decimal('total_claim', 15, 2)->nullable();  //اجمالى المطالبة المالية
            // $table->string('non_financial_claim')->nullable(); //    حقل المطالبة النصية

            $table->decimal('financial_claim', 15, 2)->nullable()->comment('المطالبة المالية');
            $table->string('non_financial_claim')->nullable()->comment('المطالبة غير المالية');
            $table->string('other_claim', 255)->nullable()->comment('مطالبة اخرى');

            $table->text('scope_of_work')->nullable()->comment('نطاق العمل');


            $table->date('contractual_closure')->nullable(); // الإغلاق التعاقدي
            $table->string('opponent_proof_number')->nullable(); // رقم إثبات الخصم
            // $table->enum('status', ['ongoing', 'completed', 'postponed', 'canceled', 'closed'])->default('ongoing');

            // $table->enum('status', [
            //     'ongoing',
            //     'completed',
            //     'postponed',
            //     'canceled',
            //     'closed',
            //     'waiting_completion'
            // ])->default('waiting_completion');

            $table->unsignedBigInteger('created_by')->nullable(); // لربط الموظف بالمستخدم
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('complate_user_id')->nullable(); // لربط الموظف بالمستخدم
            $table->foreign('complate_user_id')->nullable()->references('id')->on('users')->onDelete('cascade');

            // $table->foreignId('power_of_attorney_id')->constrained('power_of_attorneys'); //تعليق الوكالة

            $table->enum('project_type', ['consulting', 'legal', 'consulting_legal', 'old'])  // old => المشاريع التي تم جلبها من النظام القديم 
                ->comment('نوع المشروع: استشاري، قضائي، استشاري قضائي');


            $table->enum('contract_type', ['main_contract', 'exceptional_contract'])->default('main_contract');

            $table->foreignId('exceptional_contract_id')->nullable()->constrained('exceptional_contracts')->onDelete('cascade');
            $table->softDeletes(); // الحذف اللطيف
            $table->timestamps(); // تواريخ الإنشاء والتحديث
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
