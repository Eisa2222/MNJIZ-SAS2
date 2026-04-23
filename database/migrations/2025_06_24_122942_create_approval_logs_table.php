<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('approval_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_id')->constrained('approval_requests')->onDelete('cascade');
            $table->integer('level'); // المستوى الذي تم التعامل معه
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->enum('action', ['approved', 'rejected', 'revoked']);
            $table->text('reason')->nullable(); // سبب الرفض أو الإلغاء
            $table->json('additional_data')->nullable(); // بيانات إضافية قد نحتاجها
            $table->timestamps();

            // Indexes للأداء
            $table->index(['approval_request_id', 'level'], 'request_level_index');
            $table->index('employee_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_logs');
    }
};
