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
        Schema::create('campaign_management', function (Blueprint $table) {

            $table->id();
            // اسم الحملة 
            $table->text('campaign_name');

            // نوع الحملة
            $table->foreignId('content_type_id')->nullable()->constrained('settings_content_types')->onDelete('cascade'); //

            // اقسام الحملات 
            $table->foreignId('campaign_section_id')->nullable()->constrained('settings_campaign_sections')->onDelete('cascade'); //

            // الهدف من الحملة
            $table->foreignId('content_purpose_id')->nullable()->constrained('settings_content_purposes')->onDelete('cascade'); //

            // المنصة
            $table->foreignId('social_id')->nullable()->constrained('settings_socials')->onDelete('cascade'); //

            $table->decimal('budget', 10, 2)->default(0); // الميزانية الإجمالية

            $table->date('start_date');

            $table->date('end_date');

            // الجمهور المستهدف
            $table->foreignId('target_audience_id')->nullable()->constrained('settings_target_audiences')->onDelete('cascade'); //

            $table->enum('status', [
                'pending',
                'approved',
                'rejected'
            ])->default('pending');

            // النص المرئي
            $table->string('text')->nullable();

            $table->foreignId('created_by')->constrained('employees')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('employees')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_management');
    }
};
