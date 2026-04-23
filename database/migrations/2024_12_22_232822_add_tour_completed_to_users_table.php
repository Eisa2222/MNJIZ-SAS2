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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('tour_completed')->default(false); // يمكنك تغيير الموقع حسب الحاجة
            $table->boolean('tour_task_completed')->default(false); // يمكنك تغيير الموقع حسب الحاجة

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tour_completed');
            $table->dropColumn('tour_task_completed');

        });
    }
};
