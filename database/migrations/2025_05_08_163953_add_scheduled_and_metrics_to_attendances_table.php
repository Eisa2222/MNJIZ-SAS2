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
        Schema::table('attendances', function (Blueprint $table) {
            // حفظ أوقات الدوام الرسمية وقت التسجيل
            $table->time('scheduled_start_time')->nullable()->after('check_out_longitude');
            $table->time('scheduled_end_time')->nullable()->after('scheduled_start_time');
            // المقاييس الزمنية
            $table->integer('late_minutes')->default(0)->after('scheduled_end_time');
            $table->integer('early_arrival_minutes')->default(0)->after('late_minutes');
            $table->integer('early_leave_minutes')->default(0)->after('early_arrival_minutes');
            $table->integer('overtime_minutes')->default(0)->after('early_leave_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'scheduled_start_time',
                'scheduled_end_time',
                'late_minutes',
                'early_arrival_minutes',
                'early_leave_minutes',
                'overtime_minutes',
            ]);
        });
    }
};
