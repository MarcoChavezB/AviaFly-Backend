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
        Schema::create('flight_lessons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('flight_id');
            $table->unsignedBigInteger('lesson_id');
            $table->boolean('lesson_approved')->default(false);

            $table->foreign('flight_id')->references('id')->on('flight_history');
            $table->foreign('lesson_id')->references('id')->on('lessons');
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
        //
    }
};
