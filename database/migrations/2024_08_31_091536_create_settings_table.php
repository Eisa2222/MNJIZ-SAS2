<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            // $table->string('site_title')->nullable();
            $table->string('office_name')->nullable();
            // $table->string('address')->nullable();
            // $table->string('general_manager')->nullable();
            // $table->string('email')->nullable();
            // $table->string('phone')->nullable();
            // $table->string('vat')->nullable();
            // $table->string('header_image')->nullable(); // صورة الترويسة
            // $table->string('tax_registration_number')->nullable();
            // $table->string('contract_address')->nullable();
            // $table->text('contract_terms')->nullable();
            // $table->string('email_address')->nullable();
            // $table->string('app_password')->nullable();
            $table->string('colors')->nullable();
            // إضافة حقول WhatsApp
            $table->string('twilio_account_sid')->nullable();
            $table->string('twilio_auth_token')->nullable();
            $table->string('twilio_whatsapp_from')->nullable();
            $table->boolean('whatsapp_enabled')->default(false);


            $table->integer('archive_delete_duration');
            // اعدادات الطباعة
            $table->string('template_image')->nullable();
            $table->string('signature')->nullable();

            $table->boolean('maintenance_mode')->default(false);

            $table->string('logo_text')->nullable(); // إضافة حقل النص بجانب الشعار



            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
