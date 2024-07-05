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
        Schema::create('consumable_flights', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_consumable');
            $table->unsignedBigInteger('id_flight');
            $table->unsignedBigInteger('id_employee');
            $table->foreign('id_consumable')->references('id')->on('consumables');
            $table->foreign('id_flight')->references('id')->on('flight_history');
            $table->foreign('id_employee')->references('id')->on('employees');

            $table->date('date');
            $table->integer('liters');
            $table->integer('comments');
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
        Schema::dropIfExists('consumable_flights');
    }
};
