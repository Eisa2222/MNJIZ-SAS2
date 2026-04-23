<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('leave_balance_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_balance_id');
            $table->unsignedBigInteger('employee_id');
            $table->year('year');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action')->default('update');
            $table->decimal('old_total_days', 10, 2)->nullable();
            $table->decimal('new_total_days', 10, 2)->nullable();
            $table->decimal('old_used_days', 10, 2)->nullable();
            $table->decimal('new_used_days', 10, 2)->nullable();
            $table->decimal('old_remaining_days', 10, 2)->nullable();
            $table->decimal('new_remaining_days', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('leave_balance_id')->references('id')->on('leave_balances')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->index(['leave_balance_id', 'created_at']);
            $table->index(['employee_id', 'year']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balance_logs');
    }
};
