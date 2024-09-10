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
        Schema::create('stage_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_stage');
            $table->foreign('id_stage')->references('id')->on('stages')->onDelete('cascade');

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
        Schema::dropIfExists('stage_sessions');
    }
};
