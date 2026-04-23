<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSmsFieldsToSettingsTable extends Migration
{
    /**
     * تشغيل الترحيل.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('sms_provider')->nullable()->after('colors');
            $table->string('sms_api_key')->nullable()->after('sms_provider');
            $table->string('sms_api_secret')->nullable()->after('sms_api_key');
            $table->string('sms_api_endpoint')->nullable()->after('sms_api_secret');
            $table->string('sms_sender_id')->nullable()->after('sms_api_endpoint');
            $table->string('sms_from_number')->nullable()->after('sms_sender_id');
            $table->string('sms_callback_url')->nullable()->after('sms_from_number');
            $table->boolean('sms_enabled')->default(false)->after('sms_callback_url');
            $table->integer('sms_rate_limit')->nullable()->after('sms_enabled');
        });
    }

    /**
     * عكس الترحيل.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'sms_provider',
                'sms_api_key',
                'sms_api_secret',
                'sms_api_endpoint',
                'sms_sender_id',
                'sms_from_number',
                'sms_callback_url',
                'sms_enabled',
            ]);
        });
    }
}