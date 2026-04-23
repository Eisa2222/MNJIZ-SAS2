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
        Schema::create('violation_appeals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('violation_id')->constrained('violations')->onDelete('cascade');

            $table->text('appeal_reason'); // سبب التظلم

            $table->timestamp('appeal_date')->useCurrent(); // تاريخ تقديم التظلم

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->text('response')->nullable(); // الرد على التظلم


            $table->foreignId('reviewed_by')->nullable()->constrained('employees')->onDelete('cascade');
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violation_appeals');
    }
};
