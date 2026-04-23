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
        Schema::create('power_of_attorneys', function (Blueprint $table) {
            $table->id();
            $table->string('power_name');
            $table->string('power_number')->unique();
            $table->date('date_issued'); // تاريخ البداية
            $table->date('date_expiry')->nullable();
            $table->text('scope')->nullable();
            $table->enum('status', ['active', 'expired', 'revoked']);
            $table->string('file_attachment')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('power_of_attorneys');
    }
};
