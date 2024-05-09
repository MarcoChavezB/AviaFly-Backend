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
            $table->date('file');
            $table->timestamps();

            $table->unsignedBigInteger('directed_to_group');
            $table->foreign('directed_to_group')->references('id')->on('users');

            $table->bigInteger('directed_to_person');
            $table->foreign('directed_to_person')->references('id')->on('users');

            $table->unsignedBigInteger('directed_to_base');
            $table->foreign('directed_to_base')->references('id')->on('users');

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
