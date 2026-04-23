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
        Schema::create('contract_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            // العلاقة مع جدول العقود
            $table->string('name'); // اسم المرفق
            // $table->string('attachment'); // المرفق (مسار الملف)

            $table->string('file_id')->nullable();
            $table->string('file_url')->nullable();
            $table->string('download_url')->nullable();
            $table->string('share_link')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_attachments');
    }
};
