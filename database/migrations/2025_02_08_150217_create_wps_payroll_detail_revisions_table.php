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
        Schema::create('wps_payroll_detail_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wps_payroll_detail_id')->constrained('wps_payroll_details')->onDelete('cascade');

            $table->json('old_values');
            $table->json('new_values');

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->foreignId('edited_by')->constrained('employees')->onDelete('cascade');      // الموظف الذي قام بالطلب
            $table->foreignId('approved_by')->nullable()->constrained('employees')->onDelete('cascade');     // الموظف الذي قام بالاعتماد

            $table->text('notes')->nullable();
            $table->text('reply')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wps_payroll_detail_revisions');
    }
};
