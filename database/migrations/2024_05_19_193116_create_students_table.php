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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('last_names');
            $table->string('email');
            $table->string('phone');
            $table->string('cellphone');
            $table->string('curp');
            $table->decimal('credit', 8, 2)->nullable();
            $table->string('user_identification')->unique();
            $table->string('emergency_contact');
            $table->string('emergency_phone');
            $table->string('emergency_direction');
            $table->timestamps();

            $table->unsignedBigInteger('id_created_by')->nullable();
            $table->foreign('id_created_by')->references('id')->on('users');

            $table->unsignedBigInteger('id_base');
            $table->foreign('id_base')->references('id')->on('bases');

            $table->unsignedBigInteger('id_history_flight')->nullable();
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
        Schema::dropIfExists('students');
    }
};
