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
        Schema::create('lawsuit_plaintiffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawsuit_id')->constrained('lawsuits')->onDelete('cascade'); // الدعوى
            $table->unsignedBigInteger('plaintiff_id'); // معرف المدعي
            $table->string('plaintiff_type'); // نوع المدعي (يمكن أن يكون 'customer' أو 'opponent')
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lawsuit_plaintiffs');
    }
};
