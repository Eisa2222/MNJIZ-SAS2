<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdvanceNoticeDaysToSettingsLeaveTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings_leave_types', function (Blueprint $table) {
            $table->integer('advance_notice_days')->default(0)->after('max_requests')->comment('عدد ايام الإشعار المسبق المطلوبة قبل طلب الإجازة');
            $table->boolean('is_global')->default(false)->after('is_deductible');
            $table->boolean('count_weekends')->default(false)->after('is_global')->comment('هل يتم احتساب أيام الإجازة الأسبوعية ضمن أيام الإجازة');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings_leave_types', function (Blueprint $table) {
            $table->dropColumn('advance_notice_days');
        });
    }
}
