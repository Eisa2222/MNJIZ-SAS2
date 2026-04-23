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
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');


            $table->string('item_name');
            $table->text('item_description')->nullable();
            $table->integer('item_quantity')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();

            $table->timestamp('processed_at')->nullable();

            $table->softDeletes();

            $table->timestamps();
            // العلاقات مع الجداول الأخرى

            $table->foreignId('purchase_category_id')->nullable()->constrained('settings_purchase_categories')->onDelete('cascade');

            $table->foreignId('created_by')->constrained('employees')->onDelete('cascade');

            $table->foreignId('updated_by')->nullable()->constrained('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
