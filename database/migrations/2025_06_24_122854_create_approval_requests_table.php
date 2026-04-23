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
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_flow_id')->constrained('approval_flows')->onDelete('cascade');
            $table->string('approvable_type'); // نوع النموذج 
            $table->unsignedBigInteger('approvable_id'); // معرف السجل
            $table->integer('current_level')->default(1); // المستوى الحالي للاعتماد
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('requested_by_user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('completed_at')->nullable(); // وقت اكتمال الاعتماد
            $table->timestamps();

            // Indexes للأداء
            $table->index(['approvable_type', 'approvable_id'], 'approvable_index');
            $table->index(['approval_flow_id', 'status'], 'flow_status_index');
            $table->index('current_level');
            $table->index('status');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
