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
        Schema::create('supports', function (Blueprint $table) {
            $table->id(); // معرف السجل (Primary Key)

            $table->string('ticket_number')->unique(); // رقم التذكرة


            // معرف المستخدم الذي قام بإرسال الرسالة (Foreign Key)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // تصنيف التذكرة
            $table->enum('ticket_classification', ['اقتراح', 'شكوى', 'تعديلات برمجية'])->comment('تصنيف التذكرة');

            // عنوان الرسالة
            $table->string('title')->comment('عنوان الرسالة');

            // الأولوية
            $table->enum('priority', ['عاجلة', 'عالية', 'متوسطة', 'منخفضة'])->comment('أولوية التذكرة');



            // وصف الرسالة
            $table->longText('notes')->nullable()->comment('وصف الرسالة');

            // مرفقات (رابط الملف المخزن)
            $table->text('attachment')->nullable()->comment('رابط المرفقات');

            $table->longText('reply')->nullable()->comment(' الرد');
            $table->string('reply_date')->nullable()->comment(' تاريخ الرد');

            $table->enum('status', ['جديد', 'انتظار رد العميل', 'تمت المعالجة'])->default('جديد');

            $table->unsignedBigInteger('processed_by')->nullable();
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('cascade');


            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supports');
    }
};
