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
        Schema::create('template_variables', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم المتغير، مثلاً: اسم العميل
            $table->string('placeholder'); //
            $table->enum('type', ['offers', 'contracts', 'supplementary_contract', 'salary_definition', 'salary_fixation', 'training_certificate', 'clearance_certificates', 'all']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_variables');
    }
};
