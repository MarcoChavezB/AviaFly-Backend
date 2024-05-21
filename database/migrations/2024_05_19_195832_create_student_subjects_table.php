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
        Schema::create('student_subjects', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('id_student');
            $table->unsignedBigInteger('id_subject');
            $table->unsignedBigInteger('id_turn');
            $table->unsignedBigInteger('id_teacher');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('final_grade')->nullable();
            $table->double('duration')->nullable();
            $table->enum('status', ['pendings', 'approved', 'reproved'])->default('pending');

            $table->foreign('id_student')->references('id')->on('students');
            $table->foreign('')
            $table->foreign('id_subject')->references('id')->on('subjects');
            $table->foreign('id_turn')->references('id')->on('turns');
            $table->foreign('id_teacher')->references('id')->on('employees');



        });
    }


        /**
 * reproved
 * approved
 * pending
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_subjects');
    }
};
