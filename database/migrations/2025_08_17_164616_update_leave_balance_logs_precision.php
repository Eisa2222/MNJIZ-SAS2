<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('leave_balance_logs', function (Blueprint $table) {
            $table->decimal('old_total_days', 10, 4)->change();
            $table->decimal('new_total_days', 10, 4)->change();
            $table->decimal('old_used_days', 10, 4)->change();
            $table->decimal('new_used_days', 10, 4)->change();
            $table->decimal('old_remaining_days', 10, 4)->change();
            $table->decimal('new_remaining_days', 10, 4)->change();
        });
    }

    public function down()
    {
        Schema::table('leave_balance_logs', function (Blueprint $table) {
            $table->decimal('old_total_days', 10, 2)->change();
            $table->decimal('new_total_days', 10, 2)->change();
            $table->decimal('old_used_days', 10, 2)->change();
            $table->decimal('new_used_days', 10, 2)->change();
            $table->decimal('old_remaining_days', 10, 2)->change();
            $table->decimal('new_remaining_days', 10, 2)->change();
        });
    }
};
