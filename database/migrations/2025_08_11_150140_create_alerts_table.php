<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->enum('type', ['work_license_expiry', 'contract_expiry', 'training_expiry']);
            $table->enum('status', ['new', 'resolved'])->default('new');
            $table->foreignId('updated_by')->nullable()->constrained('employees');
            $table->timestamps();

            // فهارس للاستعلامات السريعة
            $table->index(['employee_id', 'type']);
            $table->index(['status', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
