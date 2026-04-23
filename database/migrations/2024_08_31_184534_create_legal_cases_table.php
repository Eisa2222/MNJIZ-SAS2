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
        Schema::create('legal_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('opponent_name');
            $table->string('opponent_phone');
            $table->string('opponent_address');
            $table->string('opponent_lawyer');
            $table->string('opponent_lawyer_phone');
            $table->foreignId('case_category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('lawyer_id')->constrained('users')->onDelete('cascade');
            $table->text('case_subject');
            $table->foreignId('litigation_stage_id')->constrained('litigation_stages')->onDelete('cascade');
            $table->enum('case_status', ['under_study', 'active', 'closed', 'pending', 'completed']);
            // $table->foreignId('court_id')->constrained('courts')->onDelete('cascade');
            $table->string('court_case_number');
            $table->date('contract_date');
            $table->decimal('contract_value', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('total_amount_including_tax', 10, 2);
            $table->text('contract_terms');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_cases');
    }
};
