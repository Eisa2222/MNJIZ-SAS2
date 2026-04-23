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
        Schema::create('custody_items', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('serial_number')->unique()->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->foreignId('asset_category_id')->constrained('settings_asset_categories')->onDelete('cascade');
            $table->foreignId('storage_location_id')->constrained('settings_storage_locations')->onDelete('cascade');

            $table->text('description')->nullable();


            $table->enum('use_status', [
                'available',
                'reserved',
                'in_use',
                'maintenance',
                'stale'
            ])->default('available');


            $table->enum('custody_status', [
                'new',
                'used',
            ])->default('new');



            $table->foreignId('created_by')->constrained('employees')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('employees')->onDelete('cascade');


            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custody_items');
    }
};
