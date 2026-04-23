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
        Schema::create('lawsuit_power_of_attorneys', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lawsuit_id')->constrained('lawsuits')->onDelete('cascade'); // معرّف الدعوى المرتبطة
            $table->foreignId('power_of_attorney_id')->constrained('power_of_attorneys')->onDelete('cascade'); // معرّف الوكالة

            $table->unique(['lawsuit_id', 'power_of_attorney_id'], 'lawsuit_po_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lawsuit_power_of_attorneys');
    }
};
