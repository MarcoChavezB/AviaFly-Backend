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
        Schema::create('student_lessons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_student');
            $table->foreign('id_student')->references('id')->on('students');

            $table->unsignedBigInteger('id_lesson');
            $table->foreign('id_lesson')->references('id')->on('lessons');

            $table->boolean('lesson_passed')->default(false);
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
        Schema::dropIfExists('student_lessons');
    }
};
