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
        Schema::create('flight_history', function (Blueprint $table) {
            $table->id();
            $table->decimal('hours', 8, 2);
            $table->enum('type_flight', ['simulador', 'vuelo']);
            $table->enum('flight_status', ['proceso', 'cancelado', 'hecho']);
            $table->enum('maneuver', ['local', 'ruta']);
            $table->enum('flight_category', ['VFR', 'IFR', 'IFR_nocturno']);
            $table->date('flight_date');
            $table->string('flight_hour');
            $table->boolean('flight_alone')->default(false);
            $table->decimal('initial_horometer', 8, 2);
            $table->decimal('final_horometer', 8, 2);
            $table->decimal('total_horometer', 8, 2);
            $table->decimal('final_tacometer', 8, 2);
            $table->string('comment')->nullable();
            $table->unsignedBigInteger('id_equipo');
            $table->foreign('id_equipo')->references('id')->on('info_flights');
            $table->unsignedBigInteger('id_session');
            $table->foreign('id_session')->references('id')->on('sessions');
            $table->unsignedBigInteger('id_airplane')->nullable();
            $table->foreign('id_airplane')->references('id')->on('air_planes')->nullable();
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
        Schema::dropIfExists('flight_history');
    }
};
