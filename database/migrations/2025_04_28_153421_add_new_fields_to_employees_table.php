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
        Schema::table('employees', function (Blueprint $table) {
            // إضافة حقل نوع الحساب البنكي
            $table->string('bank_account_type')->nullable();
            // إضافة حقل IBAN
            $table->string('iban', 50)->nullable();
            //اللقب
            $table->string('nickname', 50)->nullable();

            // هل لديه تأمينات
            $table->boolean('has_insurance')->default(false);

            // نسبة التامينات
            $table->decimal('insurance_percentage', 5, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['bank_account_type', 'iban', 'nickname']);
        });
    }
};
