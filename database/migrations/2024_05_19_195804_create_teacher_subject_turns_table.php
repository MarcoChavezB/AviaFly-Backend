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
        Schema::create('teacher_subject_turns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_teacher');
            $table->foreign('id_teacher')->references('id')->on('employees');

            $table->unsignedBigInteger('id_subject');
            $table->foreign('id_subject')->references('id')->on('subjects');

            $table->unsignedBigInteger('id_turn');
            $table->foreign('id_turn')->references('id')->on('turns');
            $table->timestamps();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('duration')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teacher_subject_turns');
    }
};
