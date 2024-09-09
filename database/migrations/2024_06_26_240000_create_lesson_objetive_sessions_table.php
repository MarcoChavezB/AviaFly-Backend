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
        Schema::create('lesson_objetive_sessions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_flight_objetive');
            $table->foreign('id_flight_objetive')->references('id')->on('flight_objetives')->onDelete('cascade');

            $table->unsignedBigInteger('id_lesson');
            $table->foreign('id_lesson')->references('id')->on('lessons')->onDelete('cascade');

            $table->unsignedBigInteger('id_session');
            $table->foreign('id_session')->references('id')->on('sessions')->onDelete('cascade');

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
        Schema::dropIfExists('lesson_objetive_sessions');
    }
};
