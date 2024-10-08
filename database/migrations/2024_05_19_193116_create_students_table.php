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
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('cellphone')->nullable();
            $table->string('curp')->unique();
            $table->decimal('credit', 8, 2)->nullable()->default(0);
            $table->decimal('flight_credit', 8, 2)->nullable()->default(0);
            $table->decimal('simulator_credit', 8, 2)->nullable()->default(0);
            $table->string('user_identification')->unique()->nullable();
            $table->string('emergency_contact');
            $table->string('emergency_phone');
            $table->string('emergency_direction');
            $table->date('start_date');
            $table->timestamps();

            $table->unsignedBigInteger('id_career');
            $table->foreign('id_career')->references('id')->on('careers')->onDelete('cascade');

            $table->unsignedBigInteger('id_base');
            $table->foreign('id_base')->references('id')->on('bases')->onDelete('cascade');

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
