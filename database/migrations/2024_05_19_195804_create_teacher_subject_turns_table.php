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
            $table->foreign('id_teacher')->references('id')->on('employees')->onDelete('cascade');

            $table->unsignedBigInteger('career_subject_id');
            $table->foreign('career_subject_id')->references('id')->on('career_subjects')->onDelete('cascade');

            $table->unsignedBigInteger('id_turn');
            $table->foreign('id_turn')->references('id')->on('turns')->onDelete('cascade');
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
        Schema::dropIfExists('teacher_subject_turns');
    }
};
