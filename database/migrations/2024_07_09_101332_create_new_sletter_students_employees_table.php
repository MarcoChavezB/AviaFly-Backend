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
        Schema::create('new_sletter_students_employees', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_new_sletter');
            $table->foreign('id_new_sletter')->references('id')->on('new_sletters');

            $table->unsignedBigInteger('id_student')->nullable();
            $table->foreign('id_student')->references('id')->on('students')->nullable();

            $table->unsignedBigInteger('id_employee')->nullable();
            $table->foreign('id_employee')->references('id')->on('employees')->nullable();

            $table->boolean('is_read')->default(false);
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
        Schema::dropIfExists('new_sletter_students');
    }
};

