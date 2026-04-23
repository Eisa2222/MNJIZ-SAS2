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
            // نوع العقد
            $table->enum('contract_type', ['specific', 'non_specific'])->default('specific');
            $table->string('law_license_number')->nullable();
            $table->date('law_license_end_date')->nullable();
            $table->enum('trial_period', [0, 90, 180])->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // حذف الأعمدة
            $table->dropColumn([
                'law_license_number',
                'law_license_end_date',
                'trial_period'
            ]);
        });
    }
};
