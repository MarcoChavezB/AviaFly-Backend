<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('last_names');
            $table->string('email')->unique();
            $table->string('company_email');
            $table->string('phone');
            $table->string('cellphone');
            $table->string('curp')->unique();
            $table->string('user_identification')->unique()->nullable();
            $table->enum('user_type', ['root', 'admin', 'employee', 'instructor']);

            $table->unsignedBigInteger('id_base');
            $table->foreign('id_base')->references('id')->on('bases');
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
        Schema::dropIfExists('employees');
    }
};
