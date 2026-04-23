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
        Schema::create('meeting_notes', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique(); // معرف الحدث من Microsoft Graph
            $table->unsignedBigInteger('user_id'); // معرف المستخدم الذي أنشأ الاجتماع
            
            $table->text('meeting_name')->nullable(); // اسم الاجتماع
            $table->text('meeting_points')->nullable(); // نقاط الاجتماع
            $table->text('meeting_outputs')->nullable(); // مخرجات الاجتماع
            
            $table->text('meeting_start_date')->nullable(); // تاريخ بداية الاجتماع
            $table->text('meeting_end_date')->nullable();   // تاريخ نهاية الاجتماع
            


            // 
            // حقل مجال الاجتماع
            $table->enum('meeting_field', ['projects', 'lawsuits', 'public'])->default('public');
            // حقل المشاريع
            $table->unsignedBigInteger('project_id')->nullable();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            // حقل الدعاوى
            $table->unsignedBigInteger('lawsuits_id')->nullable();
            $table->foreign('lawsuits_id')->references('id')->on('lawsuits')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_notes');
    }
};