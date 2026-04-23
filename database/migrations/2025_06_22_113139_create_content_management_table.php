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
        Schema::create('content_management', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_type_id')->nullable()->constrained('settings_content_types')->onDelete('cascade'); //
            $table->text('content_text')->nullable();

            // نمط النشر
            $table->foreignId('publishing_pattern_id')->nullable()->constrained('settings_publishing_patterns')->onDelete('cascade'); //

            // الهدف من القطعة
            $table->foreignId('content_purpose_id')->nullable()->constrained('settings_content_purposes')->onDelete('cascade'); //

            $table->enum('publication_status', ['draft', 'scheduled', 'published'])->default('draft');

            $table->enum('status', [
                'pending',
                'approved',
                'rejected'
            ])->default('pending');

            // نوع الوسائط
            $table->enum(
                'media_type',
                ['image', 'video', 'text', 'image_text', 'video_text']
            )->nullable();

            // رابط الوسائط (صورة أو فيديو)
            $table->string('media')->nullable();

            $table->date('publication_date')->nullable();

            // نوع الجدولة 
            $table->enum('publish_type', ['one_time', 'recurring'])->nullable();

            // نسخة أولية: وقت & تاريخ النشر لمرة واحدة
            $table->dateTime('one_time_at')->nullable();

            // نمط التكرار
            $table->enum('recurring_type', ['daily', 'weekly', 'monthly'])->nullable();
            // وقت النشر التكراري
            $table->time('publish_time')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->json('week_days')->nullable(); // أيّام الأسبوع لأسبوعي

            $table->tinyInteger('month_day')->nullable();  // يوم الشهر للـ monthly

            // للتحكم في التفعيل
            $table->boolean('is_active')->default(true);

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
        Schema::dropIfExists('content_management');
    }
};
