<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('offer_name');
            $table->string('offer_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('relationship_manager_id')->constrained('employees')->onDelete('cascade');
            $table->date('start_date');

            $table->enum('status', [
                'under_study',
                'pending',
                'approved',
                'rejected',
                'waiting_customer',
                'Contracted',
                'expired',
            ])->default('under_study');


            $table->foreignId('created_by')->constrained('employees')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('employees')->onDelete('cascade');

            $table->text('technical_offer')->nullable();
            $table->text('financial_offer')->nullable();

            $table->string('pdf_path')->nullable();
            $table->boolean('is_private_and_secret')->default(false);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
