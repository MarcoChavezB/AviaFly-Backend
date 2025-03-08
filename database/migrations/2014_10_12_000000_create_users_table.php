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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('user_identification')->unique();
            $table->enum('user_type', ['root', 'admin', 'employee', 'instructor', 'student', 'flight_instructor']);
            $table->unsignedBigInteger('id_base');
            $table->string('password');

/*             $table->boolean('is_active')->default(true)->after('password'); // Agrega la columna después de 'email' */

            /* $table->string('afac_user')->nullable();
            $table->string('afac_password')->nullable(); */
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('id_base')->references('id')->on('bases')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
