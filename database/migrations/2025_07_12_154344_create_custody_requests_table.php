<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('custody_requests', function (Blueprint $table) {
            $table->id();

            $table->enum('request_type', ['assign', 'return'])->default('assign');

            $table->foreignId('parent_request_id')->nullable()->constrained('custody_requests')->onDelete('cascade');

            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');

            $table->foreignId('custody_item_id')->constrained('custody_items')->onDelete('cascade');

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected', 'returned'])->default('pending');

            $table->enum('return_status', [
                'no_longer_needed',
                'job_completed',
                'replacement',
                'malfunction',
                'damage',
                'lost',
                'upgrade',
                'transfer',
                'end_of_contract',
                'personal_reason',
                'security_concern',
                'resignation'
            ])->nullable();

            $table->text('notes')->nullable();

            $table->foreignId('created_by')->constrained('employees')->onDelete('cascade');

            $table->foreignId('updated_by')->nullable()->constrained('employees')->onDelete('cascade');

            $table->softDeletes();

            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('custody_requests');
    }
};
