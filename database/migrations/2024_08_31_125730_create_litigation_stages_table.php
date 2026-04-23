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
        Schema::create('litigation_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('inactive'); // Status of the litigation stage
            $table->unsignedBigInteger('user_id'); // ID of the user who created the stage
            $table->softDeletes(); // Soft delete column
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('litigation_stages');
    }
};
