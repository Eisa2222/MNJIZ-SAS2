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
        Schema::create('assigned_lawsuits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lawsuit_id')
                ->constrained('lawsuits')
                ->onDelete('cascade');

            $table->foreignId('assigned_to') //المكلف
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('user_accepted_id')->nullable()
                ->constrained('users')
                ->onDelete('cascade');

            // إضافة فهرس فريد لمنع التكرار
            $table->unique(['lawsuit_id', 'assigned_to']);
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('accepted');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assigned_lawsuits');
    }
};
