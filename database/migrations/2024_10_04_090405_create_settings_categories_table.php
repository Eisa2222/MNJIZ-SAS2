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
        Schema::create('settings_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // حقل الاسم
            // $table->boolean('status')->default(1); // حقل الحالة
            $table->enum('status', ['active', 'inactive']);
            // $table->integer('order')->default('0');
            $table->unsignedBigInteger('user_id'); // حقل المستخدم
            $table->softDeletes(); // soft delete
            $table->timestamps();
            // إضافة foreign key للمستخدم
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('position')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings_categories');
    }
};
