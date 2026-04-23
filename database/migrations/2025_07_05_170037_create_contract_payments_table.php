<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');

            // نوع الحساب: نسبة أم مبلغ ثابت
            $table->enum('calculation_type', ['percentage', 'fixed'])->default('fixed');

            // نوع الدفعية
            $table->enum('payment_batch_type', ['advance', 'deferred', 'final'])->default('advance');

            $table->decimal('percentage', 5, 2)->nullable();

            $table->decimal('fixed_amount', 18, 2)->nullable();

            $table->string('currency', 3)->default('SAR');

            // تاريخ الاستحقاق
            $table->date('due_date');

            // تاريخ الدفع الفعلي
            $table->date('payment_date')->nullable();

            // طريقة الدفع وحالته
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'credit_card'])->nullable();

            $table->enum('status', ['scheduled', 'paid', 'late', 'cancelled'])->default('scheduled');

            $table->foreignId('paid_by')->nullable()->constrained('employees')->onDelete('cascade');

            $table->foreignId('created_by')->constrained('employees')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('employees')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_payments');
    }
};
