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
        Schema::create('flight_hours_restrictions', function (Blueprint $table) {
            $table->id();
            $table->string('motive');
            $table->date('start_date');
            $table->string('start_hour');
            $table->string('end_hour');
            $table->string('description');

            $table->unsignedBigInteger('id_flight');
            $table->foreign('id_flight')->references('id')->on('info_flights');
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
        Schema::dropIfExists('flight_hours_restrictions');
    }
};
