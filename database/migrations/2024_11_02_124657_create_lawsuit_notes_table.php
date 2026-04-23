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
        Schema::create('lawsuit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawsuit_id')->constrained('lawsuits')->onDelete('cascade'); // ربط الملاحظة بالدعوى
            $table->string('title'); // عنوان الملاحظة
            $table->text('text'); // نص الملاحظة
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // المستخدم الذي أضاف الملاحظة
            $table->enum('type', ['private', 'requires_manager_reply', 'public'])->default('private'); // نوع الملاحظة

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lawsuit_notes');
    }
};