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
        Schema::create('custody_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('custody_request_id')->constrained('custody_requests')->onDelete('cascade');

            $table->enum('action', ['checkout', 'checkin','checkout_canceled','checkin_canceled']);

            $table->timestamp('log_date');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custody_logs');
    }
};
