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
            $table->enum('day_status', ['present', 'leave', 'absent'])->after('overtime_minutes')->nullable()->index('idx_attendance_day_status');
            $table->date('date')->after('user_id')->index();
            // 2) تحويل check_in_time و check_out_time من TIMESTAMP إلى TIME
            $table->time('check_in_time')->nullable()->change();
            $table->time('check_out_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_attendance_day_status');
            $table->dropColumn('day_status');
        });
    }
};
