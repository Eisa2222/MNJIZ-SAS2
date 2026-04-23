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
        Schema::create('power_attorney_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('power_attorney_id')
                  ->constrained('power_of_attorneys')
                  ->onDelete('cascade');

                  
            $table->foreignId('customer_id')
                  ->constrained('customers')
                  ->onDelete('cascade');


            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->timestamps();

            // إضافة فهرس فريد لمنع التكرار
            $table->unique(['power_attorney_id', 'customer_id']);
            $table->softDeletes();



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('power_attorney_customers');
    }
};
