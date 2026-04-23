<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * تشغيل الميغريشن.
     */
    public function up()
    {
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained('settings_leave_types')->onDelete('cascade');
            $table->unsignedSmallInteger('year')->default(Carbon::now()->year)->comment('سنة احتساب الرصيد');
            $table->decimal('total_days', 8, 4)->default(0);
            $table->decimal('used_days', 8, 4)->default(0);
            $table->decimal('remaining_days', 8, 4)->default(0);
            $table->date('last_accrued_at')->nullable();
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * التراجع عن الميغريشن.
     */
    public function down()
    {
        Schema::dropIfExists('leave_balances');
    }
};
