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
        Schema::create('settings_lawsuits_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subcategory_id'); // التصنيف الفرعي
            $table->string('name'); // اسم نوع الدعوى
            // $table->boolean('status')->default(1); // حالة نوع الدعوى
            $table->enum('status', ['active', 'inactive']);
            $table->unsignedBigInteger('user_id'); // المستخدم الذي قام بإنشاء نوع الدعوى
            $table->softDeletes(); // لحذف البيانات بشكل غير دائم
            $table->timestamps();

            // إضافة foreign key للتصنيف الفرعي والمستخدم
            $table->foreign('subcategory_id')->references('id')->on('settings_subcategories')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('position')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings_lawsuits_types');
    }
};
