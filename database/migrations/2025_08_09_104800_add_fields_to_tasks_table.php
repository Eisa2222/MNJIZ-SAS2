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
        Schema::table('tasks', function (Blueprint $table) {
            // قناة التسويق
            $table->foreignId('marketing_id')->nullable()->constrained('settings_marketing_channels')->onDelete('cascade');

            // قناة التسويق التفصيلة
            $table->foreignId('detailed_marketing_channel_id')->nullable()->constrained('employees')->onDelete('cascade');

            // العملاء
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade');

            // مواقع التواصل
            $table->foreignId('social_media_id')->nullable()->constrained('settings_socials')->onDelete('cascade');


            // مجالات المهمة
            $table->foreignId('clearance_certificate_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('advance_id')->nullable()->constrained()->onDelete('cascade');

            $table->foreignId('reward_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('deduction_id')->nullable()->constrained()->onDelete('cascade');

            $table->foreignId('content_management_id')->nullable()->constrained('content_management')->onDelete('cascade');
            $table->foreignId('custody_id')->nullable()->constrained('custody_requests')->onDelete('cascade');
            $table->foreignId('leave_id')->nullable()->constrained('leave_requests')->onDelete('cascade');
            $table->foreignId('wps_id')->nullable()->constrained('wps_payrolls')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            //
        });
    }
};
