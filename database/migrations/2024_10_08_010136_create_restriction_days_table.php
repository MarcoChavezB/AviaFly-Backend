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
        Schema::create('restriction_days', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_day');
            $table->unsignedBigInteger('id_flight_restriction');

            $table->foreign('id_day')->references('id')->on('days');
            $table->foreign('id_flight_restriction')->references('id')->on('flight_hours_restrictions');
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
        Schema::dropIfExists('restriction_days');
    }
};
