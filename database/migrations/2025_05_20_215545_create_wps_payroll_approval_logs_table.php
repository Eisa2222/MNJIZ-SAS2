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
        Schema::create('wps_payroll_approval_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wps_payroll_id');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('action'); // 'created', 'approved', 'rejected', 'revoked', 'updated'
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->foreign('wps_payroll_id')->references('id')->on('wps_payrolls')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wps_payroll_approval_logs');
    }
};
