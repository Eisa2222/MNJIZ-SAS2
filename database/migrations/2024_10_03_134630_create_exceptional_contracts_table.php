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
        Schema::create('exceptional_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_name');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('cascade');
            $table->text('scope_of_work')->nullable();
            $table->text('reasons')->nullable();
            $table->text('equivalent')->nullable();
            $table->string('project_name')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('created_by_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('updated_by_id')->nullable()->constrained('employees')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exceptional_contracts');
    }
};
