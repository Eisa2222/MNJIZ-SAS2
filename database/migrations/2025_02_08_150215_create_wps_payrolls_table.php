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
        Schema::create('wps_payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();      // رقم المرجع لكل دفعة
            $table->date('run_date');                  // تاريخ حساب الرواتب
            $table->enum('status', [
                'generated_automatically',
                'generated_manually',
                'modified',

                'pending',
                'approved',
                'rejected',

                'exported_to_bank',
            ])->default('generated_automatically');

            $table->foreignId('created_by')->nullable()->constrained('employees')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('employees')->onDelete('cascade');

            $table->decimal('total_gross', 12, 2)->nullable(); // إجمالي الرواتب قبل الاستقطاعات
            $table->decimal('total_net',   12, 2)->nullable(); // إجمالي الصافي بعد الاستقطاعات
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wps_payrolls');
    }
};
