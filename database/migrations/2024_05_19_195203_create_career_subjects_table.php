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
        Schema::create('career_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_career');
            $table->foreign('id_career')->references('id')->on('careers');
            $table->unsignedBigInteger('id_subject');
            $table->foreign('id_subject')->references('id')->on('subjects');
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
        Schema::dropIfExists('career_subjects');
    }
};
