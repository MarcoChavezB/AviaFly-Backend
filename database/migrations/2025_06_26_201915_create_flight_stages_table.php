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
        Schema::create('flight_stages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_flight');
            $table->foreign('id_flight')->references('id')->on('flight_history');

            $table->unsignedBigInteger('id_stage');
            $table->foreign('id_stage')->references('id')->on('stages');
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
        Schema::dropIfExists('flight_stages');
    }
};
