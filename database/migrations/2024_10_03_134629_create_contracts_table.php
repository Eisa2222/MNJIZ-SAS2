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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_name'); // اسم العقد
            $table->string('contract_number')->unique(); // رقم العقد
            // $table->string('link_file')->nullable();
            // $table->string('file_id')->nullable();

            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('cascade'); // العميل

            $table->date('expected_closure_date'); // تاريخ الاغلاق المتوقع
            $table->date('contract_start_date'); // تاريخ بداية العقد
            $table->date('contract_end_date')->nullable(); // تاريخ نهاية العقد

            $table->foreignId('offer_id')->nullable()->constrained('offers')->onDelete('cascade'); // العرض السعري
            $table->foreignId('contract_manager_id')->nullable()->constrained('employees')->onDelete('cascade')->comment('مسؤول العقد');
            $table->foreignId('relationship_manager_id')->nullable()->constrained('employees')->onDelete('cascade')->comment('مسؤول العلاقة');

            $table->foreignId('contract_status_id')->nullable()->constrained('settings_contract_statuses')->onDelete('cascade')->comment('حالة العقد');
            // $table->string('offer_status'); // حالة العرض

            //

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->foreignId('created_by')->constrained('employees')->onDelete('cascade')->comment('الشخص الذي أنشأ العرض');
            $table->foreignId('updated_by')->nullable()->constrained('employees')->onDelete('cascade')->comment('الشخص الذي عدل العرض');

            // الحقول الجديدة
            $table->enum('contract_type', ['main', 'supplementary'])->default('main')->comment('نوع العقد: رئيسي أو ملحق');
            $table->foreignId('main_contract_id')->nullable()->constrained('contracts')->onDelete('cascade')->comment('العقد الرئيسي للعقد الملحق');



            // خاص بالعقود الملحقة
            $table->text('supplementary_technical_offer')->nullable()->comment('العرض الفني');
            $table->text('supplementary_financial_offer')->nullable()->comment('العرض المالي');


            $table->boolean('is_private_and_secret')->default(false)->comment('خاصية السري والخاص');

            $table->softDeletes(); // استخدام Soft Deletes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
