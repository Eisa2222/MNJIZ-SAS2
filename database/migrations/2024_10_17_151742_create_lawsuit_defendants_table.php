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
        Schema::create('lawsuit_defendants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawsuit_id')->constrained('lawsuits')->onDelete('cascade'); // الدعوى
            $table->unsignedBigInteger('defendant_id'); // معرف المدعى عليه
            $table->string('defendant_type'); // نوع المدعى عليه (يمكن أن يكون 'customer' أو 'opponent')
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lawsuit_defendants');
    }
};
