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
        Schema::create('company_attachments', function (Blueprint $table) {
            $table->id();

            // السجل التجاري
            $table->string('commercial_register')->nullable()->comment('ملف السجل التجاري');
            $table->date('commercial_register_end_date')->nullable()->comment('تاريخ انتهاء السجل التجاري');

            // التأمينات
            $table->string('insurance')->nullable()->comment('ملف التأمينات');
            $table->date('insurance_end_date')->nullable()->comment('تاريخ انتهاء التأمينات');

            // الغرفة التجارية
            $table->string('chamber_of_commerce')->nullable()->comment('ملف الغرفة التجارية');
            $table->date('chamber_end_date')->nullable()->comment('تاريخ انتهاء الغرفة التجارية');

            // بلدي
            $table->string('balady')->nullable()->comment('ملف بلدي');
            $table->date('balady_end_date')->nullable()->comment('تاريخ انتهاء بلدي');

            // التوطين
            $table->string('tawteen')->nullable()->comment('ملف التوطين');
            $table->date('tawteen_end_date')->nullable()->comment('تاريخ انتهاء التوطين');

            // حماية الأجور
            $table->string('wage_protection')->nullable()->comment('ملف حماية الأجور');
            $table->date('wage_protection_end_date')->nullable()->comment('تاريخ انتهاء حماية الأجور');

            // عقد التأسيس
            $table->string('incorporation_contract')->nullable()->comment('ملف عقد التأسيس');

            // العنوان الوطني
            $table->string('national_address')->nullable()->comment('ملف العنوان الوطني');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_attachments');
    }
};
