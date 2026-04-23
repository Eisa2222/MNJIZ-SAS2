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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('title')->nullable();

            $table->foreignId('nationality_id')->nullable()->constrained('settings_countries')->onDelete('cascade');

            $table->foreignId('status_id')->nullable()->constrained('settings_client_statuses')->onDelete('cascade');

            $table->text('contact_number')->nullable();

            $table->string('email')->nullable();

            $table->string('address')->nullable();

            $table->foreignId('relationship_manager_id')->constrained('employees')->onDelete('cascade');

            $table->foreignId('marketing_channel_id')->nullable()->constrained('settings_marketing_channels')->onDelete('cascade');

            $table->foreignId('detailed_marketing_channel_id')->nullable()->constrained('employees')->onDelete('cascade');

            $table->foreignId('sector_id')->nullable()->constrained('settings_sectors')->onDelete('cascade');

            $table->foreignId('parent_customer_id')->nullable()->constrained('customers')->onDelete('cascade');

            $table->foreignId('social_media_id')->nullable()->constrained('settings_socials')->onDelete('cascade');

            $table->foreignId('department_id')->nullable()->constrained('settings_department_contract_cases')->onDelete('cascade');


            $table->string('civil_registry_number')->nullable();

            $table->string('commercial_registration_number')->nullable();

            $table->string('unified_number')->nullable();

            $table->enum('customer_type', ['individual', 'company'])->default('individual');

            // user_id
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
        Schema::dropIfExists('customers');
    }
};
