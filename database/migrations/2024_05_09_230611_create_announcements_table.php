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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('content', 255);
            $table->string('file');
            $table->enum('directed_to_group', ['admin', 'employee', 'instructor', 'student']);
            $table->timestamps();

            $table->unsignedBigInteger('id_directed_to_person');
            $table->foreign('id_directed_to_person')->references('id')->on('users');

            $table->unsignedBigInteger('id_directed_to_base');
            $table->foreign('id_directed_to_base')->references('id')->on('bases');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('announcements');
    }
};
