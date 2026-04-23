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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('microsoft_client_id')->nullable();
            $table->text('microsoft_client_secret')->nullable(); // استخدم نص لتخزين البيانات المشفرة
            $table->string('microsoft_redirect_uri')->nullable();
            $table->string('microsoft_tenant_id')->nullable();
            $table->string('main_email')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'microsoft_client_id',
                'microsoft_client_secret',
                'microsoft_redirect_uri',
                'microsoft_tenant_id',
                'main_email'
            ]);
        });
    }
};
