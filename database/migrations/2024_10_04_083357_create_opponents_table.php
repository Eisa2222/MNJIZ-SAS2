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
        Schema::create('opponents', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // الاسم
            $table->string('email')->nullable()->unique(); // الايميل ويجب أن يكون فريدًا
            $table->text('phone')->nullable(); // الهاتف
            $table->text('bio')->nullable(); // نبذة
            $table->foreignId('settings_region_id')->nullable()->constrained('settings_regions')->onDelete('cascade'); // العنوان
            $table->enum('type', ['individual', 'company']); // النوع: أفراد أو مؤسسة
            $table->string('commercial_registration')->nullable(); // السجل التجاري
            $table->string('unified_number')->nullable(); // الرقم الموحد
            $table->string('identity_number')->nullable(); // رقم الهوية

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
        Schema::dropIfExists('opponents');
    }
};
