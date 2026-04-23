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
        Schema::create('social_publications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('content_management_id')->constrained('content_management')->onDelete('cascade');

            $table->enum('platform', ['linkedin', 'x']);
            $table->dateTime('scheduled_for');
            $table->string('platform_post_id')->nullable();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['content_management_id', 'platform', 'scheduled_for'],
                'pub_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_publications');
    }
};
