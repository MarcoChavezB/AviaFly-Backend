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
            $table->foreign('id_student')->references('id')->on('students')->onDelete('cascade');

            $table->unsignedBigInteger('id_lesson_objetive');
            $table->foreign('id_lesson_objetive')->references('id')->on('lesson_objetive_sessions')->onDelete('cascade');

            $table->unsignedBigInteger('id_session');
            $table->foreign('id_session')->references('id')->on('sessions')->onDelete('cascade');

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
