<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('id_number')->nullable(); // رقم الهوية
            $table->string('commercial_record')->nullable(); // رقم السجل التجاري
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('phone')->nullable();
            $table->string('landline')->nullable(); // الهاتف الأرضي
            $table->string('email')->unique();
            $table->string('nationality')->nullable();
            $table->string('password');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable(); // الملاحظات
            $table->string('image')->nullable(); // الملاحظات
            $table->softDeletes(); // softdelete
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}

