<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBiostationFieldsToSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('biostation_api_key')->nullable(); // استبدل 'existing_field' بالحقل الذي تريد أن تظهر بعده
            $table->string('biostation_api_url')->nullable()->default('https://api.biostation.com')->after('biostation_api_key');
            $table->string('biostation_device_ip')->nullable()->after('biostation_api_url');
            $table->integer('biostation_device_port')->nullable()->default(80)->after('biostation_device_ip');
            $table->string('biostation_timezone')->nullable()->default('UTC')->after('biostation_device_port');
            $table->string('biostation_device_name')->nullable()->after('biostation_timezone');
            $table->integer('biostation_sync_interval')->nullable()->default(5)->after('biostation_device_name'); // بالفترات بالدقائق
            $table->timestamp('biostation_last_sync')->nullable()->after('biostation_sync_interval');

            // إضافة حقول مواعيد العمل
            $table->time('work_start_time')->nullable()->after('biostation_sync_interval');
            $table->time('work_end_time')->nullable()->after('work_start_time');

            // إضافة حقل لتفعيل الحضور اليدوي
            $table->boolean('manual_attendance_enabled')->default(false)->after('work_end_time');

            // location fields
            $table->decimal('company_latitude', 10, 7)->nullable()->after('manual_attendance_enabled');
            $table->decimal('company_longitude', 10, 7)->nullable()->after('company_latitude');
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'biostation_api_key',
                'biostation_api_url',
                'biostation_device_ip',
                'biostation_device_port',
                'biostation_timezone',
                'biostation_device_name',
                'biostation_sync_interval',
                'biostation_last_sync',
                'work_start_time',
                'work_end_time',
                'manual_attendance_enabled'
            ]);
        });
    }
}
