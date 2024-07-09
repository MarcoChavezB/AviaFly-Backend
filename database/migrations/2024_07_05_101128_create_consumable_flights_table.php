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
            $table->unsignedBigInteger('id_plane');
            $table->unsignedBigInteger('id_employee');
            $table->foreign('id_consumable')->references('id')->on('consumables');
            $table->foreign('id_plane')->references('id')->on('air_planes');
            $table->foreign('id_employee')->references('id')->on('employees');

            $table->date('date');
            $table->string('hour');
            $table->integer('liters');
            $table->string('comments');
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
