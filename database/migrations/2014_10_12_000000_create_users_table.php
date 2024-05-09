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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('last_name');
            $table->string('user_identification')->unique();
            $table->string('photo');
            $table->string('phone');
            $table->string('cellphone');
            $table->string('curp');
            $table->string('email');
            $table->timestamp('emergency_contact');
            $table->string('emergency_phone');
            $table->string('emergency_direction');
            $table->string('user_type');
            $table->string('password');
            $table->string('credit');
            $table->rememberToken();
            $table->timestamps();

            $table->unsignedBigInteger('id_created_by');
            $table->foreign('id_created_by')->references('id')->on('users');

            $table->unsignedBigInteger('id_base');
            $table->foreign('id_base')->references('id')->on('bases');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
