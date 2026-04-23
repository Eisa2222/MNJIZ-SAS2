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
        Schema::create('campaign_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('campaign_id')->constrained('campaign_management')->onDelete('cascade'); //

            $table->date('report_date');
            // الإنفاق الفعلي
            $table->decimal('spend', 12, 2)->nullable();
            // مرات الظهور
            $table->bigInteger('impressions')->nullable();
            // النقرات
            $table->bigInteger('clicks')->nullable();
            // معدل النقر CTR
            $table->decimal('ctr', 5, 2)->nullable();
            // تكلفة النقرة CPC
            $table->decimal('cpc', 8, 2)->nullable();
            // عدد التحويلات
            $table->bigInteger('conversions')->nullable();
            // قيمة التحويلات
            $table->decimal('conversion_value', 12, 2)->nullable();
            //العائد على الإنفاق ROAS
            $table->decimal('roas', 8, 2)->nullable();

            // ملاحظات
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->constrained('employees')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('employees')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_results');
    }
};
