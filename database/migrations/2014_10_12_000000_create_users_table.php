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
            $table->string('middle_name');
            $table->string('user_identification')->unique();
            $table->string('photo');
            $table->string('phone');
            $table->string('cellphone');
            $table->string('curp');
            $table->string('email');
            $table->timestamp('emergency_contact');
            $table->string('emergency_phone');
            $table->string('emergency_direction');
            $table->enum('user_type', ['root', 'admin', 'employee', 'instructor', 'student']);
            $table->string('password');
            $table->decimal('credit', 8, 2);
            $table->rememberToken();
            $table->timestamps();

            $table->unsignedBigInteger('id_created_by');
            $table->foreign('id_created_by')->references('id')->on('users');

            $table->unsignedBigInteger('id_base');
            $table->foreign('id_base')->references('id')->on('bases');

            $table->unsignedBigInteger('id_carrier')->nullable();
            $table->foreign('id_carrier')->references('id')->on('careers');

            $table->unsignedBigInteger('id_history_flight');
            $table->foreign('id_history_flight')->references('id')->on('flight_history');
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
