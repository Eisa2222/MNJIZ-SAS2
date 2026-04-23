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
        Schema::create('deductions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('deduction_number')->unique();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->enum('deduction_type', [
                'violation',
                'advance_pay',
                'other_payments',
                'other'
            ]);
            $table->decimal('amount', 15, 2);
            $table->date('deduction_date');
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'executed'
            ])->default('pending');
            $table->foreignId('created_by')->constrained('employees')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('employees')->onDelete('cascade');
            $table->string('notes', 500)->nullable();

            $table->timestamp('applied_to_salary_date')->nullable();
            // ربط مع دورة الراتب
            $table->foreignId('wps_payrolls_id')->nullable()->constrained('wps_payrolls')->onDelete('set null');


            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deductions');
    }
};
