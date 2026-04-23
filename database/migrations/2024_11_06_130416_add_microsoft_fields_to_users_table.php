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
        Schema::table('users', function (Blueprint $table) {
            $table->string('microsoft_id')->unique()->nullable();
            $table->text('microsoft_token')->nullable();
            $table->text('microsoft_refresh_token')->nullable();
            $table->timestamp('microsoft_token_expires')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['microsoft_id', 'microsoft_token', 'microsoft_refresh_token', 'microsoft_token_expires']);
        });
    }
};
