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
        Schema::create('lawsuit_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawsuit_id')->constrained('lawsuits')->onDelete('cascade'); // الدعوى المرتبطة
            $table->string('attachment_name'); // اسم المرفق
            $table->string('file_path'); // مسار الملف (المرفق نفسه)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lawsuit_attachments');
    }
};
